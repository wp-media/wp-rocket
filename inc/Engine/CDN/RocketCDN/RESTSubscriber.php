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
	 * Rest controller instance.
	 *
	 * @var Rest
	 */
	private $rest;

	/**
	 * Subscription controller instance.
	 *
	 * @var SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * Constructor
	 *
	 * @param CDNOptionsManager      $cdn_options CDNOptionsManager instance.
	 * @param Options_Data           $options     WP Rocket Options instance.
	 * @param Rest                   $rest        Rest controller instance.
	 * @param SubscriptionController $subscription_controller Subscription controller.
	 */
	public function __construct( CDNOptionsManager $cdn_options, Options_Data $options, Rest $rest, SubscriptionController $subscription_controller ) {
		$this->cdn_options             = $cdn_options;
		$this->options                 = $options;
		$this->rest                    = $rest;
		$this->subscription_controller = $subscription_controller;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rest_api_init'                        => [
				[ 'register_enable_route' ],
				[ 'register_disable_route' ],
				[ 'register_routes' ],
			],
			'rocket_cdnfree_website_create_status' => 'check_status',
		];
	}

	/**
	 * Registers RocketCDN pages and state REST routes.
	 *
	 * @since 3.22
	 *
	 * @return void
	 */
	public function register_routes(): void {
		$this->rest->register_routes();
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
					// RocketCDN CNAME is no longer required as it's now changed on the filter level not in the settings option.
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
		$this->cdn_options->enable();

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

	/**
	 * Check subscription creation status.
	 *
	 * @param string $task_id Task ID to check.
	 * @return void
	 */
	public function check_status( string $task_id ) {
		if ( empty( $task_id ) ) {
			return;
		}
		$this->subscription_controller->check_status( $task_id );
	}
}
