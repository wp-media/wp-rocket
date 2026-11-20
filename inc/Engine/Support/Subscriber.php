<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Support;

use WP_Rocket\Dependencies\GroupOne\WapClient\AppPasswordManager;
use WP_Rocket\Dependencies\GroupOne\WapClient\Bootstrap;
use WP_Rocket\Dependencies\GroupOne\WapClient\WapClient;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\User;

class Subscriber implements Subscriber_Interface {

	/**
	 * Live URLs. Define WP_ROCKET_WAP_SERVER_URL and WP_ROCKET_WAP_ISSUER_URL to use another environment, for example staging.
	 */
	private const WAP_SERVER_URL = 'https://wordpress-agentic-platform-production.public-default.k8spod4-cph3.ingress.k8s.g1i.one';
	private const WAP_ISSUER_URL = 'https://wp-rocket.me/wp-json/grnd/v1/token';

	private const TERMS_URL   = 'https://wp-rocket.me/terms/';
	private const PRIVACY_URL = 'https://wp-rocket.me/privacy-policy/';
	// The privacy policy stands in until there is a page explaining the assistant's access to the site.
	private const SITE_ACCESS_URL = self::PRIVACY_URL;

	// The chat library product the column is registered under, and whose Application Passwords are revoked.
	private const PRODUCT = 'wprocket';

	// Permission ids. WPRocketUninstall deletes the user meta the chat library stores them under.
	private const PERMISSION_READ_PLUGIN   = 'read-plugin';
	private const PERMISSION_MANAGE_PLUGIN = 'manage-plugin';
	private const PERMISSION_MANAGE_SITE   = 'manage-site';

	/**
	 * Rest instance
	 *
	 * @var Rest
	 */
	private $rest;

	/**
	 * Meta instance
	 *
	 * @var Meta
	 */
	private $meta;

	/**
	 * Plugin options.
	 *
	 * @var Options_Data
	 */
	private Options_Data $options;

	/**
	 * User instance.
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Whether the chat column was registered in this request.
	 *
	 * @var bool
	 */
	private $chat_registered = false;

	/**
	 * Instantiate the class
	 *
	 * @param Rest         $rest Rest instance.
	 * @param Meta         $meta Meta instance.
	 * @param Options_Data $options Plugin options.
	 * @param User         $user User instance.
	 */
	public function __construct( Rest $rest, Meta $meta, Options_Data $options, User $user ) {
		$this->rest    = $rest;
		$this->meta    = $meta;
		$this->options = $options;
		$this->user    = $user;
	}

	/**
	 * Events this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'plugins_loaded'                => [ 'boot_wap_client', 20 ],
			'init'                          => 'load_wap_client_textdomain',
			'rest_api_init'                 => 'register_support_route',
			'rocket_buffer'                 => [ 'add_meta_generator', PHP_INT_MAX ],
			'admin_init'                    => 'register_column',
			'admin_notices'                 => 'maybe_display_unavailable_notice',
			'rocket_ai_assistant_available' => 'filter_assistant_available',
			'rocket_localize_admin_script'  => 'add_localize_script_data',
			'rocket_deactivation'           => 'revoke_app_passwords',
		];
	}

	/**
	 * Registers the rest support route
	 *
	 * @since 3.7.5
	 *
	 * @return void
	 */
	public function register_support_route() {
		$this->rest->register_route();
	}

	/**
	 * Add the WP Rocket meta generator tag to the HTML
	 *
	 * @param string $html The HTML content.
	 * @return string
	 */
	public function add_meta_generator( $html ): string {
		return $this->meta->add_meta_generator( $html );
	}

	/**
	 * Boots the WAP chat library: registers its AJAX handlers.
	 *
	 * The library is bundled under WP Rocket's namespace prefix, so it does not boot itself.
	 *
	 * @return void
	 */
	public function boot_wap_client(): void {
		Bootstrap::boot();
	}

	/**
	 * Loads the WAP chat library's translations.
	 *
	 * @return void
	 */
	public function load_wap_client_textdomain(): void {
		Bootstrap::load_textdomain();
	}

	/**
	 * Registers the WAP support chat column on the WP Rocket settings screen.
	 *
	 * The column is registered only when the configuration is available and WordPress Application Passwords can
	 * be used. The menu and the floating link follow the result, see is_chat_registered().
	 *
	 * @return void
	 */
	public function register_column(): void {
		$this->chat_registered = false;

		$config = $this->get_column_config();

		if ( empty( $config ) ) {
			return;
		}

		if ( ! AppPasswordManager::are_app_passwords_available() ) {
			return;
		}

		WapClient::register_chat_column( $config );

		$this->chat_registered = true;
	}

	/**
	 * Checks whether the support chat column was registered in this request.
	 *
	 * Only meaningful after the `admin_init` action, which registers it.
	 *
	 * @return bool
	 */
	public function is_chat_registered(): bool {
		return $this->chat_registered;
	}

	/**
	 * Checks whether the customer can use the assistant, ignoring the Application Passwords.
	 *
	 * For screens without the chat column, such as the admin bar. The licence key and the user data are
	 * checked first and cannot be overridden by the filters.
	 *
	 * @return bool
	 */
	public function has_assistant(): bool {
		if ( ! $this->can_use_assistant() ) {
			return false;
		}

		/**
		 * Filters whether the WP Rocket support assistant is available.
		 *
		 * True opens the support chat from the Support menu, the "Need Help?" controls and the floating
		 * button. False replaces them with links to the contact page and the documentation: the Support menu,
		 * the admin bar and the floating "Ask Support" button open the contact page, and "Need Help?" opens
		 * its article. A licence key that fails the local check or user data that does not allow the assistant
		 * make it false whatever the filters return. The same value is passed to the admin script.
		 *
		 * @param bool $available True when the assistant is available. Default true.
		 *
		 * @return bool
		 */
		if ( ! wpm_apply_filters_typed( 'boolean', 'rocket_ai_assistant_available', true ) ) {
			return false;
		}

		return $this->is_chat_filter_on();
	}

	/**
	 * Revokes the support chat's Application Passwords, for every user and on every site.
	 *
	 * @return void
	 */
	public function revoke_app_passwords(): void {
		WapClient::revoke_app_passwords( self::PRODUCT );
	}

	/**
	 * Gets the configuration of the WAP support chat column.
	 *
	 * @return array<string, mixed> The column configuration, or an empty array when the assistant is not available or the licence data is missing.
	 */
	public function get_column_config(): array {
		if ( ! $this->has_assistant() ) {
			return [];
		}

		$license_key = $this->options->get( 'consumer_key', '' );
		$email       = $this->options->get( 'consumer_email', '' );

		if ( empty( $license_key ) || empty( $email ) ) {
			return [];
		}

		// translators: Keep {contact}…{/contact} and {docs}…{/docs} as they are: they mark the words that become links.
		$connection_error = __( 'The AI Assistant is temporarily unavailable. Please try again in a few minutes. You can alternatively {contact}contact the support team{/contact} or access {docs}the documentation{/docs}.', 'rocket' );

		return [
			'id'              => 'wp-rocket-support',
			'product'         => self::PRODUCT,
			// The wp-rocket stylesheet exists from wap-client 2.5.3 (Gravity 5.52.4). Older clients load an empty one and the launcher disappears.
			'brand'           => 'wp-rocket',
			// Names the launcher (its title, shown as its text by the stylesheet) and the panel for screen readers.
			'i18n'            => [
				// translators: Text of the floating button that opens the support chat.
				'columnLabel'       => __( 'Ask Support', 'rocket' ),
				'settings'          => __( 'Settings', 'rocket' ),
				'closeAssistant'    => __( 'Close', 'rocket' ),
				'consentText'       => __( 'Please review and accept the {terms} for the WP Rocket Assistant.', 'rocket' ),
				'consentAccessText' => __( 'The Assistant uses a safe {link}WordPress Application Password{/link} with limited permissions, which you can remove at any time.', 'rocket' ),
				// translators: Keep {contact}…{/contact} and {docs}…{/docs} as they are: they mark the words that become links.
				'consentRequired'   => __( 'You need to accept the Terms & Conditions to use the AI Assistant. You can alternatively {contact}contact the support team{/contact} or access {docs}the documentation{/docs}.', 'rocket' ),
				'errorUnavailable'  => $connection_error,
				'errorAuthFailed'   => $connection_error,
			],
			// WAP selects the agent role `wprocket:support`. For an expired license the token does not list that role,
			// so WAP keeps the customer on its fallback agent (see the premium_roles claim of the token issuer).
			'page_context'    => 'support',
			'capability'      => 'rocket_manage_options',
			'server_url'      => rocket_get_constant( 'WP_ROCKET_WAP_SERVER_URL', false )
				? rocket_get_constant( 'WP_ROCKET_WAP_SERVER_URL', false )
				: self::WAP_SERVER_URL,
			'grnd'            => [
				'issuer_url'    => rocket_get_constant( 'WP_ROCKET_WAP_ISSUER_URL', false )
					? rocket_get_constant( 'WP_ROCKET_WAP_ISSUER_URL', false )
					: self::WAP_ISSUER_URL,
				'license_key'   => $license_key . '|' . $email,
				// Gives each WordPress user their own chat history instead of one shared per site.
				'send_user_ref' => true,
			],
			'terms_url'       => self::TERMS_URL,
			'privacy_url'     => self::PRIVACY_URL,
			'site_access_url' => self::SITE_ACCESS_URL,
			// Fill the {contact} and {docs} links of the messages above. A library without link support shows the raw markers.
			'contact_url'     => rocket_get_external_url( 'contact' ),
			'docs_url'        => get_rocket_documentation_url(),
			'screens'         => [ 'settings_page_wprocket' ],
			// The chat connects only when the column is opened; older libraries silently drop the unknown startOnOpen key.
			'column'          => [
				'side'        => 'right',
				'startOnOpen' => true,
			],
			'layout'          => [
				// WAP picks the agent from the license through the token, so customers don't pick one.
				'showAgentSelector' => false,
			],
			// The ids are bare: the wp-rocket.me token issuer adds the `wprocket:` namespace and drops ids it doesn't allow.
			'permissions'     => $this->get_permission_rows(),
		];
	}

	/**
	 * Gets the permission rows shown in the support chat's settings.
	 *
	 * WAP maps each ability to a row: `read-plugin` for WP Rocket's `get-` abilities, which are exactly its
	 * read-only ones; `manage-plugin` for WP Rocket's other abilities; `manage-site` for every other ability,
	 * from other plugins and core. The last two only offer Deny, so the library can only store deny for
	 * them (locked server-side); `disabled` merely greys them out in the UI.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function get_permission_rows(): array {
		return [
			[
				'id'           => self::PERMISSION_READ_PLUGIN,
				// translators: Label of the support chat setting deciding whether the assistant may read WP Rocket's settings.
				'label'        => __( 'Read WP Rocket settings', 'rocket' ),
				'options'      => [
					[
						'value' => 'allow',
						'label' => _x( 'Allow', 'support chat permission option', 'rocket' ),
					],
					[
						'value' => 'ask',
						'label' => _x( 'Ask permission', 'support chat permission option', 'rocket' ),
					],
					[
						'value' => 'deny',
						'label' => _x( 'Deny', 'support chat permission option', 'rocket' ),
					],
				],
				'defaultValue' => 'allow',
			],
			$this->get_locked_permission_row(
				self::PERMISSION_MANAGE_PLUGIN,
				// translators: Label of the support chat setting deciding whether the assistant may change WP Rocket's settings.
				__( 'Change WP Rocket settings', 'rocket' )
			),
			$this->get_locked_permission_row(
				self::PERMISSION_MANAGE_SITE,
				// translators: Label of the support chat setting deciding whether the assistant may read and change the rest of the WordPress site.
				__( 'Read & Write WordPress configuration', 'rocket' )
			),
		];
	}

	/**
	 * Gets a permission row that is not available yet: locked to Deny and marked as coming soon.
	 *
	 * @param string $id    Permission id.
	 * @param string $label Row label.
	 *
	 * @return array<string, mixed>
	 */
	private function get_locked_permission_row( string $id, string $label ): array {
		return [
			'id'           => $id,
			'label'        => $label,
			'options'      => [
				[
					'value' => 'deny',
					'label' => _x( 'Deny', 'support chat permission option', 'rocket' ),
				],
			],
			'defaultValue' => 'deny',
			'disabled'     => true,
			// translators: Badge on a support chat setting that cannot be changed yet.
			'badge'        => __( 'Coming soon', 'rocket' ),
		];
	}

	/**
	 * Explains on the settings page why the support chat can't connect when WordPress Application Passwords are unavailable.
	 *
	 * The chat authenticates with an Application Password, so without one the widget can only fail to connect.
	 *
	 * @return void
	 */
	public function maybe_display_unavailable_notice(): void {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! $screen || 'settings_page_wprocket' !== $screen->id || empty( $this->get_column_config() ) ) {
			return;
		}

		if ( AppPasswordManager::are_app_passwords_available() ) {
			return;
		}

		$reason_code = AppPasswordManager::unavailable_reason();

		switch ( $reason_code ) {
			case 'multisite':
				$reason = __( 'The support assistant is only available when WP Rocket is network-activated on this multisite network.', 'rocket' );
				break;
			case 'https':
				$reason = __( 'The support assistant requires HTTPS to use WordPress Application Passwords. It will be available once this site is served over HTTPS.', 'rocket' );
				break;
			default:
				$reason = __( 'The support assistant is unavailable because WordPress Application Passwords have been disabled on this site, for example by a security plugin. Enable Application Passwords to use it.', 'rocket' );
		}

		rocket_notice_html(
			[
				'status'      => 'warning',
				'dismissible' => '',
				'message'     => sprintf(
					'<strong>%1$s</strong>: %2$s',
					WP_ROCKET_PLUGIN_NAME,
					$reason
				),
			]
		);
	}

	/**
	 * Callback of the `rocket_ai_assistant_available` filter.
	 *
	 * Makes the value false when the licence key fails the local check, the user data does not allow the
	 * assistant or the `rocket_support_chat_available` filter is false. The value is passed to the admin script.
	 *
	 * @param bool $available True when the assistant is available.
	 *
	 * @return bool
	 */
	public function filter_assistant_available( $available ): bool {
		return (bool) $available && $this->can_use_assistant() && $this->is_chat_filter_on();
	}

	/**
	 * Passes whether the assistant is available to the admin script.
	 *
	 * The script receives the value as a string, '1' or ''.
	 *
	 * @param array $data Localize script data.
	 *
	 * @return array
	 */
	public function add_localize_script_data( $data ): array {
		$data = (array) $data;

		/** This filter is documented in inc/Engine/Support/Subscriber.php */
		$data['assistant_available'] = wpm_apply_filters_typed( 'boolean', 'rocket_ai_assistant_available', true );

		return $data;
	}

	/**
	 * Checks the licence key locally, then the assistant access of the user.
	 *
	 * Not memoized: the key check is local and the shared user is kept current by the refresh of the customer data.
	 *
	 * @return bool
	 */
	private function can_use_assistant(): bool {
		if ( ! rocket_valid_key() ) {
			return false;
		}

		return $this->user->can_use_ai();
	}

	/**
	 * Applies the `rocket_support_chat_available` filter.
	 *
	 * @return bool
	 */
	private function is_chat_filter_on(): bool {
		/**
		 * Filters whether the WAP support chat is part of the assistant.
		 *
		 * Returning false behaves like `rocket_ai_assistant_available` returning false: the chat column and
		 * the notice about unavailable Application Passwords are removed and the contact page and documentation
		 * links take over.
		 *
		 * @param bool $available True to make the support chat available. Default true.
		 *
		 * @return bool
		 */
		return wpm_apply_filters_typed( 'boolean', 'rocket_support_chat_available', true );
	}
}
