<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

/**
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_CreateSubscription extends AbstractSubscriptionControllerTestCase {

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

		// The success path deliberately leaves this transient set (see the assertion
		// below), so it must be cleared here or it leaks into any other test that runs
		// afterward in the same process and also has a token set (e.g. it makes
		// SubscriptionController::get_subscription_data() short-circuit to [] via
		// is_subscription_creation_loading(), silently breaking is_paid()/is_free()
		// for unrelated tests such as CdnStateBridge's backfill test).
		delete_transient( 'rocket_cdn_subscription_creation_in_progress' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, array $expected ): void {
		$customer_data = (object) [
			'rocketcdn' => (object) [
				'cdn_free_url' => 'https://rocketcdn.me/api/website/create-free/',
			],
		];

		set_transient( 'wp_rocket_customer_data', $customer_data );

		$container = $this->getRocketContainer();
		$container->get( 'user' )->set_user( $customer_data );
		$this->subscription_controller = $container->get( 'rocketcdn_subscription_controller' );

		if ( ! empty( $config['free_pages'] ) ) {
			$query = $container->get( 'rocketcdn_query' );
			foreach ( $config['free_pages'] as $page ) {
				$query->add_item(
					[
						'url'           => $page['url'],
						'title'         => $page['title'],
						'modified'      => current_time( 'mysql' ),
						'last_accessed' => current_time( 'mysql' ),
					]
				);
			}
		}

		$this->mock_api( $config );

		$result = $this->subscription_controller->create_subscription( true );

		if ( $expected['is_error'] ) {
			$this->assertInstanceOf( \WP_Error::class, $result );
			$this->assertFalse( get_transient( 'rocket_cdn_subscription_creation_in_progress' ) );
		} else {
			$this->assertTrue( $result );
			$this->assertSame( $expected['token'], get_option( 'rocketcdn_user_token' ) );
			$this->assertNotFalse( get_transient( 'rocket_cdn_subscription_creation_in_progress' ) );
		}
	}
}
