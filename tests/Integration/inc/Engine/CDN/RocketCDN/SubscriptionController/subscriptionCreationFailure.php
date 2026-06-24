<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

use WP_Rocket\Tests\Integration\TestCase;

/**
 *
 * Scenario: After a cancel + delete (outside grace period), when the plugin
 * attempts to auto-create a free subscription, the creation API returns an
 * error. The loader transient must be cleaned up and the failure must be
 * propagated so no success path runs.
 *
 *
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_SubscriptionCreationFailure extends TestCase {

	protected static $use_settings_trait = true;

	protected static $transients = [
		'rocketcdn_status'          => null,
		'rocket_cdn_website_search' => null,
	];

	public const TOKEN = '1234567890123456789012345678901234567890';

	private $subscription_controller;

	public function set_up() {
		parent::set_up();

		$container = apply_filters( 'rocket_container', null );

		$this->subscription_controller = $container->get( 'rocketcdn_subscription_controller' );

		delete_transient( 'rocketcdn_status' );
		delete_transient( 'rocket_cdn_website_search' );
		delete_transient( 'rocket_cdn_subscription_creation_in_progress' );
		delete_transient( 'rocket_cdn_create_request' );
		delete_transient( 'rocket_cdn_check_status_request' );
		delete_option( 'rocketcdn_user_token' );
		delete_option( 'rocket_rocketcdn_forced_pause_state' );
		delete_transient( 'wp_rocket_customer_data' );

		$settings             = get_option( 'wp_rocket_settings', [] );
		$settings['cdn']      = 1;
		$settings['cdn_type'] = 'rocketcdn';
		update_option( 'wp_rocket_settings', $settings );

		update_option( 'rocketcdn_user_token', self::TOKEN );

		set_current_screen( 'settings_page_wprocket' );
		add_filter( 'home_url', [ $this, 'home_url_cb' ] );
	}

	public function tear_down() {
		remove_all_filters( 'pre_http_request' );
		remove_filter( 'home_url', [ $this, 'home_url_cb' ] );

		delete_transient( 'rocketcdn_status' );
		delete_transient( 'rocket_cdn_website_search' );
		delete_transient( 'rocket_cdn_subscription_creation_in_progress' );
		delete_transient( 'rocket_cdn_create_request' );
		delete_option( 'rocketcdn_user_token' );
		delete_option( 'rocket_rocketcdn_forced_pause_state' );
		delete_transient( 'wp_rocket_customer_data' );

		set_current_screen( 'front' );

		parent::tear_down();
	}

	public function home_url_cb(): string {
		return 'http://example.org';
	}

	public function testShouldDoAsExpected(): void {
		set_transient(
			'wp_rocket_customer_data',
			(object) [
				'rocketcdn' => (object) [
					'cdn_free_url' => 'https://rocketcdn.me/api/website/create-free/',
				],
			]
		);

		add_filter(
			'pre_http_request',
			function ( $preempt, $args, $url ) {
				// Create endpoint → simulate a server error.
				if ( false !== strpos( $url, 'https://rocketcdn.me/api/website/create-free/' ) ) {
					return new \WP_Error( 'http_request_failed', 'Internal Server Error' );
				}
				// subscription/status → 404 (no active subscription).
				if ( false !== strpos( $url, 'https://rocketcdn.me/api/subscription/' ) ) {
					return [
						'response' => [ 'code' => 404, 'message' => 'Not Found' ],
						'body'     => '',
					];
				}
				return $preempt;
			},
			10,
			3
		);

		$result = $this->subscription_controller->create_subscription( true );

		$this->assertFalse( get_transient( 'rocket_cdn_subscription_creation_in_progress' ) );

		$this->assertInstanceOf( \WP_Error::class, $result );
	}
}
