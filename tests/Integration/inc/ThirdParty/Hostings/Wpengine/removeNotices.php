<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Wpengine;

use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\CapTrait;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Wpengine::remove_notices
 *
 * @group  Wpengine
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
	}

	public function testShouldCleanWPEngine() {
		remove_action( 'admin_init', [ self::$wpengine, 'run_rocket_bot_after_wpengine' ] );

		$this->assertTrue( (bool) has_action( 'admin_notices', [ self::$subscriber, 'notice_advanced_cache_permissions' ] ) );

		do_action( 'admin_init' );

		$this->assertFalse( has_action( 'admin_notices', [ self::$subscriber, 'notice_advanced_cache_permissions' ] ) );
		$this->assertFalse( has_action( 'admin_notices', [
			self::$subscriber,
			'notice_advanced_cache_content_not_ours',
		] ) );
	}
}
