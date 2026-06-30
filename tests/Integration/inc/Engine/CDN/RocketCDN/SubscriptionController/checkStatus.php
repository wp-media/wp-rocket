<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

/**
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_CheckStatus extends AbstractSubscriptionControllerTestCase {

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
				'cdn'      => 0,
				'cdn_type' => 'rocketcdn',
			]
		);
		$this->set_rocketcdn_user_token();
	}

	public function tear_down() {
		self::truncateRocketCDNTable();

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, array $expected ): void {
		// Simulate the state left by a successful create_subscription() call.
		set_transient( 'rocket_cdn_subscription_creation_in_progress', [ 'task_id' => 'task_abc_123' ] );

		$this->mock_api( $config );

		$this->subscription_controller->check_status( 'task_abc_123' );

		$transient = get_transient( 'rocket_cdn_subscription_creation_in_progress' );
		$this->assertSame( $expected['pending_transient'], false !== $transient );

		$settings = $this->options_api->get( 'settings', [] );
		$this->assertSame( $expected['cdn_enabled'], (int) ( $settings['cdn'] ?? 0 ) );
	}
}
