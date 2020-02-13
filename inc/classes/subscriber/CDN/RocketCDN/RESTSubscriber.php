<?php
namespace WP_Rocket\Subscriber\CDN\RocketCDN;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

/**
 * Subscriber for RocketCDN REST API Integration
 *
 * @since 3.5
 * @author Remy Perona
 */
class RESTSubscriber implements Subscriber_Interface {
	const ROUTE_NAMESPACE = 'wp-rocket/v1';

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
	 * {@inheritdoc}
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
			self::ROUTE_NAMESPACE,
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
						'validate_callback' => function( $param ) {
							$url = esc_url_raw( $param );

							return ! empty( $url );
						},
						'sanitize_callback' => function( $param ) {
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
			self::ROUTE_NAMESPACE,
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
		$this->options_api->set( 'rocketcdn_active', 1 );

		delete_transient( 'rocketcdn_status' );

		$response = [
			'code'    => 'success',
			'message' => __( 'RocketCDN enabled', 'rocket' ),
			'data'    => [
				'status' => 200,
			],
		];

		return rest_ensure_response( $response );
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
		$this->options_api->set( 'rocketcdn_active', 0 );

		delete_option( 'rocketcdn_user_token' );
		delete_transient( 'rocketcdn_status' );

		$response = [
			'code'    => 'success',
			'message' => __( 'RocketCDN disabled', 'rocket' ),
			'data'    => [
				'status' => 200,
			],
		];

		return rest_ensure_response( $response );
	}

	/**
	 * Checks that the email sent along the request corresponds to the one saved in the DB
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @param string $param Parameter value to validate.
	 * @return bool
	 */
	public function validate_email( $param ) {
		return $param === $this->options->get( 'consumer_email' );
	}

	/**
	 * Checks that the key sent along the request corresponds to the one saved in the DB
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @param string $param Parameter value to validate.
	 * @return bool
	 */
	public function validate_key( $param ) {
		return $param === $this->options->get( 'consumer_key' );
	}
}
