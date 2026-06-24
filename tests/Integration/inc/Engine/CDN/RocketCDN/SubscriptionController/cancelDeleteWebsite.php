<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

use ReflectionProperty;
use WP_Rocket\Tests\Integration\TestCase;

/**
 *
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_CancelDeleteWebsite extends TestCase {

	protected static $use_settings_trait = true;

	protected static $transients = [
		'rocketcdn_status'          => null,
		'rocket_cdn_website_search' => null,
	];

	const TOKEN = '1234567890123456789012345678901234567890';

	private $subscription_controller;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::installRocketCDNTable();
	}

	public static function tear_down_after_class() {
		self::uninstallRocketCDNTable();
		parent::tear_down_after_class();
	}

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

		// Reset the FrontendSubscriber's per-request memoized CDN URL.
		$frontend = $container->get( 'rocketcdn_frontend_subscriber' );
		$prop     = new ReflectionProperty( $frontend, 'rocketcdn_url' );
		$prop->setValue( $frontend, null );

		self::truncateRocketCDNTable();

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
		delete_transient( 'rocket_cdn_check_status_request' );
		delete_option( 'rocketcdn_user_token' );
		delete_option( 'rocket_rocketcdn_forced_pause_state' );
		delete_transient( 'wp_rocket_customer_data' );

		self::truncateRocketCDNTable();
		set_current_screen( 'front' );

		parent::tear_down();
	}

	public function home_url_cb(): string {
		return 'http://example.org';
	}


	public function testShouldDoAsExpected(): void {
		$query = apply_filters( 'rocket_container', null )->get( 'rocketcdn_query' );
		$query->add_item(
			[
				'url'           => 'http://example.org/',
				'title'         => 'Home',
				'modified'      => current_time( 'mysql' ),
				'last_accessed' => current_time( 'mysql' ),
			]
		);

		$cdn_token = 'newtoken12345678901234567890123456789';
		$task_id   = 'task_abc_123';

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
			function ( $preempt, $args, $url ) use ( $cdn_token, $task_id ) {
				if ( preg_match( '#https://rocketcdn\.me/api/subscription/[^/]+/status#', $url ) ) {
					return [
						'response' => [ 'code' => 404, 'message' => 'Not Found' ],
						'body'     => '',
					];
				}

				if ( false !== strpos( $url, 'https://rocketcdn.me/api/website/search/' ) ) {
					return [
						'response' => [ 'code' => 404, 'message' => 'Not Found' ],
						'body'     => '',
					];
				}

				if ( false !== strpos( $url, 'https://rocketcdn.me/api/website/create-free/' ) ) {
					return [
						'response' => [ 'code' => 200, 'message' => 'OK' ],
						'body'     => wp_json_encode(
							[
								'success' => true,
								'data'    => [
									'code'      => 'cdn_task_enqueued',
									'task_id'   => $task_id,
									'cdn_token' => $cdn_token,
								],
							]
						),
					];
				}

				// Task-status endpoint – returns SUCCESS.
				if ( false !== strpos( $url, 'https://rocketcdn.me/api/website/task/' ) ) {
					return [
						'response' => [ 'code' => 200, 'message' => 'OK' ],
						'body'     => wp_json_encode(
							[
								'success' => true,
								'status'  => 'SUCCESS',
							]
						),
					];
				}

				return $preempt;
			},
			10,
			3
		);


		$result = $this->subscription_controller->create_subscription( true );

		$this->assertTrue( $result );

		$this->assertSame( $cdn_token, get_option( 'rocketcdn_user_token' ) );

		$this->assertNotFalse( get_transient( 'rocket_cdn_subscription_creation_in_progress' ) );

		$this->subscription_controller->check_status( $task_id );

		$this->assertFalse( get_transient( 'rocket_cdn_subscription_creation_in_progress' ) );

		$settings = get_option( 'wp_rocket_settings', [] );
		$this->assertSame( 1, (int) ( $settings['cdn'] ?? 0 ) );
	}
}
