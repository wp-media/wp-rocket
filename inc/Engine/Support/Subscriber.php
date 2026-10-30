<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Support;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\API\UserClient;

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
	 * User client instance.
	 *
	 * @var UserClient
	 */
	private $user_client;

	/**
	 * Instantiate the class
	 *
	 * @param Rest         $rest Rest instance.
	 * @param Meta         $meta Meta instance.
	 * @param Options_Data $options Plugin options.
	 * @param UserClient   $user_client User client instance.
	 */
	public function __construct( Rest $rest, Meta $meta, Options_Data $options, UserClient $user_client ) {
		$this->rest        = $rest;
		$this->meta        = $meta;
		$this->options     = $options;
		$this->user_client = $user_client;
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
			'wap_client_capability'         => 'get_rocket_capability',
			'rocket_ai_assistant_available' => 'filter_assistant_available',
			'rocket_localize_admin_script'  => 'add_localize_script_data',
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
			'product'         => 'wprocket',
			// The design system only publishes stylesheets for some brands. An unknown brand loads an empty one and the launcher disappears.
			'brand'           => 'group-one',
			// WAP selects the agent role `wprocket:support`. For an expired license the token does not list that role,
			// so WAP keeps the customer on its fallback agent (see the premium_roles claim of the token issuer).
			'page_context'    => 'support',
			'server_url'      => rocket_get_constant( 'WP_ROCKET_WAP_SERVER_URL', false )
				? rocket_get_constant( 'WP_ROCKET_WAP_SERVER_URL', false )
				: self::WAP_SERVER_URL,
			'grnd'            => [
				'issuer_url'  => rocket_get_constant( 'WP_ROCKET_WAP_ISSUER_URL', false )
					? rocket_get_constant( 'WP_ROCKET_WAP_ISSUER_URL', false )
					: self::WAP_ISSUER_URL,
				'license_key' => $license_key . '|' . $email,
			],
			'terms_url'       => self::TERMS_URL,
			'privacy_url'     => self::PRIVACY_URL,
			'site_access_url' => self::SITE_ACCESS_URL,
			'screens'         => [ 'settings_page_wprocket' ],
			'column'          => [ 'side' => 'right' ],
			'layout'          => [
				// WAP picks the agent from the license through the token, so customers don't pick one.
				'showAgentSelector' => false,
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

		$reason = \GroupOne\WapClient\AppPasswordManager::is_https_missing()
			? __( 'The support assistant requires HTTPS to use WordPress Application Passwords. It will be available once this site is served over HTTPS.', 'rocket' )
			: __( 'The support assistant is unavailable because WordPress Application Passwords have been disabled on this site, for example by a security plugin. Enable Application Passwords to use it.', 'rocket' );

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
	 * Maps the WAP capability to WP Rocket's own capability so no separate activation is needed.
	 *
	 * The filter passes the default WAP capability, which is not needed: this always returns ours.
	 *
	 * @return string
	 */
	public function get_rocket_capability(): string {
		return 'rocket_manage_options';
	}

	/**
	 * Callback of the `rocket_ai_assistant_available` filter.
	 *
	 * The filter controls the "Need Help?" controls and the value passed to the admin script. It is true by default
	 * and false when the licence key fails the local check or the user data does not allow the assistant. The chat
	 * column is decided by the same check in get_column_config() and does not apply this filter.
	 *
	 * @since 3.24
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
	 * @since 3.24
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
	 * Not memoized: the key check is local and the user data is one cached read, so a refresh is seen at once.
	 *
	 * @return bool
	 */
	private function can_use_assistant(): bool {
		if ( ! rocket_valid_key() ) {
			return false;
		}

		return ( new User( $this->user_client->get_user_data() ) )->can_use_ai();
	}
}
