<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Wpengine;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\CapTrait;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Wpengine::run_rocket_bot_after_wpengine
 * @uses   ::rocket_has_constant
 * @uses   ::rocket_get_constant
 * @uses   ::run_rocket_bot
 * @uses   ::run_rocket_sitemap_preload
 *
 * @group  Wpengine
 * @group  ThirdParty
 */
class Test_RunRocketAfterWpengine extends AdminTestCase {
	use CapTrait;

	protected      $user_id = 0;
	private static $subscriber;
	private static $wpengine;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		CapTrait::hasAdminCapBeforeClass();
		CapTrait::setAdminCap();

		$container        = apply_filters( 'rocket_container', null );
		self::$subscriber = $container->get( 'admin_cache_subscriber' );
		self::$wpengine   = $container->get( 'wpengine' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		CapTrait::resetAdminCap();
	}

	public function setup() {
		parent::setup();

		$this->user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $this->user_id );
	}

	public function testShouldRunRocketAfterWpengine() {
		Functions\expect( 'check_admin_referer' )
			->once()
			->andReturn( true );

		Functions\expect( 'run_rocket_bot' )->once();
		Functions\expect( 'run_rocket_sitemap_preload' )->once();

		do_action( 'admin_init' );
	}
}
