<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Support;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;

class Subscriber implements Subscriber_Interface {

	private const WAP_SERVER_URL = 'https://wordpress-agentic-platform-staging.public-default.k8spod4-cph3.ingress.k8s.g1i.one';
	private const WAP_ISSUER_URL = 'https://www-sandbox2.wp-rocket.me/wp-json/grnd/v1/token';

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
	 * Instantiate the class
	 *
	 * @param Rest         $rest Rest instance.
	 * @param Meta         $meta Meta instance.
	 * @param Options_Data $options Plugin options.
	 */
	public function __construct( Rest $rest, Meta $meta, Options_Data $options ) {
		$this->rest    = $rest;
		$this->meta    = $meta;
		$this->options = $options;
	}

	/**
	 * Events this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rest_api_init'         => 'register_support_route',
			'rocket_buffer'         => [ 'add_meta_generator', PHP_INT_MAX ],
			'admin_init'            => 'register_column',
			'admin_notices'         => 'maybe_display_unavailable_notice',
			'wap_client_capability' => 'get_rocket_capability',
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
	 * @return array<string, mixed> The column configuration, or an empty array when the licence data is missing.
	 */
	public function get_column_config(): array {
		$license_key = $this->options->get( 'consumer_key', '' );
		$email       = $this->options->get( 'consumer_email', '' );

		if ( empty( $license_key ) || empty( $email ) ) {
			return [];
		}

		return [
			'id'              => 'wp-rocket-support',
			'product'         => 'wprocket',
			'brand'           => 'wprocket',
			// WAP selects the agent role `wprocket:support` from the product and this page context.
			'page_context'    => 'support',
			'server_url'      => wpm_apply_filters_typed( 'string', 'rocket_wap_server_url', self::WAP_SERVER_URL ),
			'grnd'            => [
				'issuer_url'  => wpm_apply_filters_typed( 'string', 'rocket_wap_issuer_url', self::WAP_ISSUER_URL ),
				'license_key' => $license_key . '|' . $email,
			],
			'terms_url'       => self::TERMS_URL,
			'privacy_url'     => self::PRIVACY_URL,
			'site_access_url' => self::SITE_ACCESS_URL,
			'screens'         => [ 'settings_page_wprocket' ],
			'column'          => [ 'side' => 'right' ],
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
	 * @param string $_capability The default WAP capability name (unused; we always return ours).
	 * @return string
	 */
	public function get_rocket_capability( string $_capability ): string {
		return 'rocket_manage_options';
	}
}
