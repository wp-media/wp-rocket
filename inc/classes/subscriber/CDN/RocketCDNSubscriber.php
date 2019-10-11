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
				'methods'  => 'PUT',
				'callback' => [ $this, 'enable' ],
				'args'     => [
					'email' => [
						'required'          => true,
						'validate_callback' => [ $this, 'validate_email' ],
					],
					'key'   => [
						'required'          => true,
						'validate_callback' => [ $this, 'validate_key' ],
					],
					'url'   => [
						'required'          => true,
						'validate_callback' => function( $param, $request, $key ) {
							$url = esc_url_raw( $param );

							return ! empty( $url );
						},
						'sanitize_callback' => function( $param, $request, $key ) {
							return esc_url_raw( $param );
						},
					],
				],
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
				'methods'  => 'PUT',
				'callback' => [ $this, 'disable' ],
				'args'     => [
					'email' => [
						'required'          => true,
						'validate_callback' => [ $this, 'validate_email' ],
					],
					'key'   => [
						'required'          => true,
						'validate_callback' => [ $this, 'validate_key' ],
					],
				],
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
	 * @return string
	 */
	public function enable( \WP_REST_Request $request ) {
		$params = $request->get_body_params();

		$cnames   = [];
		$cnames[] = $params['url'];

		$this->options->set( 'cdn', 1 );
		$this->options->set( 'cdn_cnames', $cnames );
		$this->options->set( 'cdn_zone', [ 'all' ] );

		$this->options_api->set( 'settings', $this->options->get_options() );

		return rest_ensure_response( __( 'RocketCDN Enabled', 'rocket' ) );
	}

	/**
	 * Disable the CDN and remove the RocketCDN URL from WP Rocket options
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @param \WP_REST_Request $request the WP Rest Request object.
	 * @return string
	 */
	public function disable( \WP_REST_Request $request ) {
		$this->options->set( 'cdn', 0 );
		$this->options->set( 'cdn_cnames', [] );
		$this->options->set( 'cdn_zone', [] );

		$this->options_api->set( 'settings', $this->options->get_options() );

		return rest_ensure_response( __( 'RocketCDN disabled', 'rocket' ) );
	}

	/**
	 * Checks that the email sent along the request corresponds to the one saved in the DB
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @param string           $param Parameter value to validate.
	 * @param \WP_REST_Request $request WP REST Request object.
	 * @param string           $key Parameter key.
	 * @return bool
	 */
	public function validate_email( $param, $request, $key ) {
		return $param === $this->options->get( 'consumer_email' );
	}

	/**
	 * Checks that the key sent along the request corresponds to the one saved in the DB
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @param string           $param Parameter value to validate.
	 * @param \WP_REST_Request $request WP REST Request object.
	 * @param string           $key Parameter key.
	 * @return bool
	 */
	public function validate_key( $param, $request, $key ) {
		return $param === $this->options->get( 'consumer_key' );
	}
}
