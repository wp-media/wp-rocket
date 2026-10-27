<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Support;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;

class Subscriber implements Subscriber_Interface {

	private const WAP_SERVER_URL = 'https://wordpress-agentic-platform-staging.public-default.k8spod4-cph3.ingress.k8s.g1i.one';
	private const WAP_ISSUER_URL = 'https://www-sandbox2.wp-rocket.me/wp-json/grnd/v1/token';

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

		$license_key = $this->options->get( 'consumer_key', '' );
		$email       = $this->options->get( 'consumer_email', '' );

		if ( empty( $license_key ) || empty( $email ) ) {
			return;
		}

		\WapClient::register_chat_column(
			[
				'id'         => 'wp-rocket-support',
				'product'    => 'wprocket',
				'server_url' => wpm_apply_filters_typed( 'string', 'rocket_wap_server_url', self::WAP_SERVER_URL ),
				'grnd'       => [
					'issuer_url'  => wpm_apply_filters_typed( 'string', 'rocket_wap_issuer_url', self::WAP_ISSUER_URL ),
					'license_key' => $license_key . '|' . $email,
				],
				'screens'    => [ 'settings_page_wprocket' ],
				'column'     => [ 'side' => 'right' ],
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
