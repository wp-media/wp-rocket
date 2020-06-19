<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\WPEngine;

use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\CapTrait;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\WPEngine::remove_notices
 *
 * @group  WPEngine
 * @group  ThirdParty
 */
class Test_RemoveNotices extends AdminTestCase {
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

		remove_action( 'admin_init', [ self::$wpengine, 'run_rocket_bot_after_wpengine' ] );
	}

	public function tearDown() {
		parent::tearDown();

		add_action( 'admin_init', [ self::$wpengine, 'run_rocket_bot_after_wpengine' ] );
	}

	public function testShouldCleanWPEngine() {
		// Set up before state.
		$this->assertTrue( (bool) has_action( 'admin_notices', [ self::$subscriber, 'notice_advanced_cache_permissions' ] ) );

		// Run it.
		do_action( 'admin_init' );

		// Check that both callbacks were unregistered.
		$this->assertFalse( has_action( 'admin_notices', [ self::$subscriber, 'notice_advanced_cache_permissions' ] ) );
	}
}
