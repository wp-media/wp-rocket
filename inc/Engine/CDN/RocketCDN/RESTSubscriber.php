<?php
namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for RocketCDN REST API Integration
 *
 * @since 3.5
 */
class RESTSubscriber implements Subscriber_Interface {
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
	 * @param Rest                   $rest                    Rest controller instance.
	 * @param SubscriptionController $subscription_controller Subscription controller.
	 */
	public function __construct( Rest $rest, SubscriptionController $subscription_controller ) {
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
			'rest_api_init'                               => 'register_routes',
			'rocket_cdnfree_website_create_status'        => 'check_status',
			'rocket_cdnfree_subscription_creation_failed' => 'handle_subscription_creation_failed',
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
	 * Rolls back a failed CDN provisioning attempt.
	 *
	 * Fired by rocket_cdnfree_subscription_creation_failed, which SubscriptionController
	 * triggers when check_status() receives a non-success response from the CDN API.
	 *
	 * @return void
	 */
	public function handle_subscription_creation_failed(): void {
		$this->rest->rollback_failed_subscription();
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
