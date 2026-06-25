<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

/**
 *
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_CancelDeleteWebsite extends AbstractSubscriptionControllerTestCase {

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

		self::truncateRocketCDNTable();

		$this->update_rocketcdn_settings(
			[
				'cdn'      => 1,
				'cdn_type' => 'rocketcdn',
			]
		);
		$this->set_rocketcdn_user_token();
	}

	public function tear_down() {
		self::truncateRocketCDNTable();

		parent::tear_down();
	}

	public function testShouldDoAsExpected(): void {
		$query = $this->getRocketContainer()->get( 'rocketcdn_query' );
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

		$customer_data = (object) [
			'rocketcdn' => (object) [
				'cdn_free_url' => 'https://rocketcdn.me/api/website/create-free/',
			],
		];

		set_transient( 'wp_rocket_customer_data', $customer_data );

		$container = $this->getRocketContainer();
		$container->get( 'user' )->set_user( $customer_data );
		$this->subscription_controller = $container->get( 'rocketcdn_subscription_controller' );

		add_filter(
			'pre_http_request',
			function ( $preempt, $args, $url ) use ( $cdn_token, $task_id ) {
				if ( preg_match( '#https://rocketcdn\.me/api/subscription/[^/]+/status#', $url ) ) {
					return [
						'response' => [
							'code'    => 404,
							'message' => 'Not Found',
						],
						'body'     => '',
					];
				}

				if ( false !== strpos( $url, 'https://rocketcdn.me/api/website/search/' ) ) {
					return [
						'response' => [
							'code'    => 404,
							'message' => 'Not Found',
						],
						'body'     => '',
					];
				}

				if ( false !== strpos( $url, 'https://rocketcdn.me/api/website/create-free/' ) ) {
					return [
						'response' => [
							'code'    => 200,
							'message' => 'OK',
						],
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
						'response' => [
							'code'    => 200,
							'message' => 'OK',
						],
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

		$settings = $this->options_api->get( 'settings', [] );
		$this->assertSame( 1, (int) ( $settings['cdn'] ?? 0 ) );
	}
}
