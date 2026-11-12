<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Support;

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
	 * Registers the WAP support chat column on the WP Rocket settings screen.
	 *
	 * @return void
	 */
	public function register_column(): void {
		if ( ! class_exists( 'WapClient' ) ) {
			return;
		}

		$config = $this->get_column_config();

		if ( empty( $config ) ) {
			return;
		}

		\WapClient::register_chat_column( $config );
	}

	/**
	 * Revokes the support chat's Application Passwords, for every user and on every site.
	 *
	 * @return void
	 */
	public function revoke_app_passwords(): void {
		if ( ! class_exists( 'WapClient' ) ) {
			return;
		}

		\WapClient::revoke_app_passwords( self::PRODUCT );
	}

	/**
	 * Gets the configuration of the WAP support chat column.
	 *
	 * @return array<string, mixed> The column configuration, or an empty array when the licence data is missing or the assistant is not available.
	 */
	public function get_column_config(): array {
		$license_key = $this->options->get( 'consumer_key', '' );
		$email       = $this->options->get( 'consumer_email', '' );

		if ( empty( $license_key ) || empty( $email ) ) {
			return [];
		}

		if ( ! $this->can_use_assistant() ) {
			return [];
		}

		return [
			'id'              => 'wp-rocket-support',
			'product'         => self::PRODUCT,
			// The wp-rocket stylesheet exists from wap-client 2.5.3 (Gravity 5.52.4). Older clients load an empty one and the launcher disappears.
			'brand'           => 'wp-rocket',
			// Names the launcher (its title, shown as its text by the stylesheet) and the panel for screen readers.
			'i18n'            => [
				// translators: Text of the floating button that opens the support chat.
				'columnLabel' => __( 'Ask Support', 'rocket' ),
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
	 * `manage-plugin` governs WP Rocket's own abilities. `manage-site` governs every other ability,
	 * from other plugins and core. It only offers Deny, so the library can only store deny for it (locked
	 * server-side); `disabled` merely greys it out in the UI.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function get_permission_rows(): array {
		return [
			[
				'id'           => self::PERMISSION_MANAGE_PLUGIN,
				// translators: Label of the support chat setting deciding whether the assistant may act on WP Rocket.
				'label'        => __( 'Manage your WP Rocket plugin', 'rocket' ),
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
			[
				'id'           => self::PERMISSION_MANAGE_SITE,
				// translators: Label of the support chat setting deciding whether the assistant may act on the rest of the site.
				'label'        => __( 'Manage your WordPress site', 'rocket' ),
				'options'      => [
					[
						'value' => 'deny',
						'label' => _x( 'Deny', 'support chat permission option', 'rocket' ),
					],
				],
				'defaultValue' => 'deny',
				'disabled'     => true,
			],
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
		if ( ! class_exists( '\GroupOne\WapClient\AppPasswordManager' ) || ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! $screen || 'settings_page_wprocket' !== $screen->id || empty( $this->get_column_config() ) ) {
			return;
		}

		if ( \GroupOne\WapClient\AppPasswordManager::are_app_passwords_available() ) {
			return;
		}

		// An older library copy loaded by another plugin may lack unavailable_reason().
		if ( method_exists( '\GroupOne\WapClient\AppPasswordManager', 'unavailable_reason' ) ) { // @phpstan-ignore-line
			$reason_code = \GroupOne\WapClient\AppPasswordManager::unavailable_reason();
		} else {
			$reason_code = \GroupOne\WapClient\AppPasswordManager::is_https_missing() ? 'https' : 'disabled';
		}

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
	 * The filter controls the "Need Help?" controls and the value passed to the admin script. It is true by default
	 * and false when the licence key fails the local check or the user data does not allow the assistant. The chat
	 * column is decided by the same check in get_column_config() and does not apply this filter.
	 *
	 * @param bool $available True when the assistant entries should be shown.
	 *
	 * @return bool
	 */
	public function filter_assistant_available( $available ): bool {
		return (bool) $available && $this->can_use_assistant();
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

		/** This filter is documented in inc/Engine/Admin/Settings/Render.php */
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
}
