<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Wpengine;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Wpengine::remove_notices
 * @group  Wpengine
 * @group  ThirdParty
 */
class Test_RemoveNotices extends TestCase {
	protected      $user_id = 0;
	private static $subscriber;
	private static $wpengine;

	public static function setUpBeforeClass() {
		remove_action( 'admin_init', '_maybe_update_core' );
		remove_action( 'admin_init', '_maybe_update_plugins' );
		remove_action( 'admin_init', '_maybe_update_themes' );

		$container = apply_filters( 'rocket_container', null );
		self::$subscriber = $container->get( 'admin_cache_subscriber' );
		self::$wpengine   = $container->get( 'wpengine' );
	}

	public function setup() {
		parent::setup();
		$this->user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		$admin = get_role( 'administrator' );

		$admin->add_cap( 'rocket_manage_options' );

		wp_set_current_user( $this->user_id );
	}

	public function tearDown() {
		parent::tearDown();
		set_current_screen( 'front' );
		if ( $this->user_id > 0 ) {
			wp_delete_user( $this->user_id );
		}
	}

	public function testShouldCleanWPEngine() {
		remove_action( 'admin_init', [ self::$wpengine, 'run_rocket_bot_after_wpengine' ] );

		do_action( 'admin_init' );

		$this->assertFalse( has_action( 'admin_notices', [ self::$subscriber, 'notice_advanced_cache_permissions' ] ) );
		$this->assertFalse( has_action( 'admin_notices', [ self::$subscriber, 'notice_advanced_cache_content_not_ours' ] ) );
	}
}
