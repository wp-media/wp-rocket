<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\AdminSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Subscriber::enqueue_admin_cpcss_heartbeat_script
 * @uses   ::rocket_get_constant
 *
 * @group  AdminOnly
 * @group  CriticalPath
 */
class Test_EnqueueAdminCpcssHeartbeatScript extends TestCase {
	use ProviderTrait;

	protected static $class_name = 'Admin';
	private static $user_id;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		CapTrait::setAdminCap();

		self::$user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
	}

	public function tearDown() {
		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'setCPCSSOption' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldEnqueueAdminScript( $config, $expected ) {
		wp_set_current_user( static::$user_id );

		Functions\when( 'wp_create_nonce' )->justReturn( 'wp_cpcss_heartbeat_nonce' );

		$this->async_css = $config['options']['async_css'];
		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'setCPCSSOption' ] );

		$wp_scripts = wp_scripts();
		$wp_scripts->init();

		do_action( 'admin_enqueue_scripts' );

		if ( $expected ) {
			$this->assertArrayHasKey( 'wpr-heartbeat-cpcss-script', $wp_scripts->registered );
			$this->assertArrayHasKey( 'data', $wp_scripts->registered['wpr-heartbeat-cpcss-script']->extra );
		} else {
			$this->assertArrayNotHasKey( 'wpr-heartbeat-cpcss-script', $wp_scripts->registered );
		}
	}

	public function setCPCSSOption() {
		return $this->async_css;
	}
}
