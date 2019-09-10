<?php
namespace WP_Rocket\Subscriber\CDN;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

/**
 * Subscriber for RocketCDN integration
 *
 * @since 3.5
 * @author Remy Perona
 */
class RocketCDNSubscriber implements Subscriber_Interface {
	/**
	 * WP Options API instance
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * WP Rocket Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param Options      $options_api WP Options API instance.
	 * @param Options_Data $options     WP Rocket Options instance.
	 */
	public function __construct( Options $options_api, Options_Data $options ) {
		$this->options_api = $options_api;
		$this->options     = $options;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'rest_api_init' => [
				[ 'register_enable_route' ],
				[ 'register_disable_route' ],
			],
		];
	}

	/**
	 * Register Enable route in the WP REST API
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function register_enable_route() {
		register_rest_route(
			'wp-rocket/v1',
			'rocketcdn/enable',
			[
				'method'   => 'PUT',
				'callback' => [ $this, 'enable' ],
			]
		);
	}

	/**
	 * Register Disable route in the WP REST API
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function register_disable_route() {
		register_rest_route(
			'wp-rocket/v1',
			'rocketcdn/disable',
			[
				'method'   => 'PUT',
				'callback' => [ $this, 'disable' ],
			]
		);
	}

	/**
	 * Enable CDN and add RocketCDN URL to WP Rocket options
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @param \WP_REST_Request $request the WP REST Request object.
	 * @return void
	 */
	public function enable( \WP_REST_Request $request ) {
		$params = $request->get_body_params();

		$this->options->set( 'cdn', 1 );
		$this->options->set( 'cdn_cnames' [ $params['rocketcdn_url'] ] );
		$this->options->set( 'cdn_zones', [ 'all' ] );

		$this->options_api->set( $this->options_api->get_option_name( 'settings' ), $this->options->get_options() );
	}

	/**
	 * Disable the CDN and remove the RocketCDN URL from WP Rocket options
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @param \WP_REST_Request $request the WP Rest Request object.
	 * @return void
	 */
	public function disable( \WP_REST_Request $request ) {
		$this->options->set( 'cdn', 0 );
		$this->options->set( 'cdn_cnames' [] );
		$this->options->set( 'cdn_zones', [] );

		$this->options_api->set( $this->options_api->get_option_name( 'settings' ), $this->options->get_options() );
	}
}
