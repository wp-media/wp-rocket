<?php
namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;

/**
 * Subscriber for RocketCDN REST API Integration
 *
 * @since 3.5
 */
class RESTSubscriber implements Subscriber_Interface {
	const ROUTE_NAMESPACE = 'wp-rocket/v1';

	/**
	 * CDNOptionsManager instance
	 *
	 * @var CDNOptionsManager
	 */
	private $cdn_options;

	/**
	 * WP Rocket Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param CDNOptionsManager $cdn_options CDNOptionsManager instance.
	 * @param Options_Data      $options     WP Rocket Options instance.
	 */
	public function __construct( CDNOptionsManager $cdn_options, Options_Data $options ) {
		$this->cdn_options = $cdn_options;
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
	 *
	 * @return void
	 */
	public function register_enable_route() {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'rocketcdn/enable',
			[
				'methods'             => 'PUT',
				'callback'            => [ $this, 'enable' ],
				'args'                => [
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
						'validate_callback' => function ( $param ) {
							$url = esc_url_raw( $param );

							return ! empty( $url );
						},
						'sanitize_callback' => function ( $param ) {
							return esc_url_raw( $param );
						},
					],
				],
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * Register Disable route in the WP REST API
	 *
	 * @since 3.5
	 *
	 * @return void
	 */
	public function register_disable_route() {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'rocketcdn/disable',
			[
				'methods'             => 'PUT',
				'callback'            => [ $this, 'disable' ],
				'args'                => [
					'email' => [
						'required'          => true,
						'validate_callback' => [ $this, 'validate_email' ],
					],
					'key'   => [
						'required'          => true,
						'validate_callback' => [ $this, 'validate_key' ],
					],
				],
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * Enable CDN and add RocketCDN URL to WP Rocket options
	 *
	 * @since 3.5
	 *
	 * @param \WP_REST_Request $request the WP REST Request object.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function enable( \WP_REST_Request $request ) {
		$params = $request->get_body_params();

		$this->cdn_options->enable( $params['url'] );

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
	 *
	 * @param \WP_REST_Request $request the WP Rest Request object.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function disable( \WP_REST_Request $request ) {
		$this->cdn_options->disable();

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
	 *
	 * @param string $param Parameter value to validate.
	 *
	 * @return bool
	 */
	public function validate_email( $param ) {
		return ! empty( $param ) && $param === $this->options->get( 'consumer_email' );
	}

	/**
	 * Checks that the key sent along the request corresponds to the one saved in the DB
	 *
	 * @since 3.5
	 *
	 * @param string $param Parameter value to validate.
	 *
	 * @return bool
	 */
	public function validate_key( $param ) {
		return ! empty( $param ) && $param === $this->options->get( 'consumer_key' );
	}
}
