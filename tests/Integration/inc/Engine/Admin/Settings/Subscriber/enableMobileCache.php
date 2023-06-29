<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Settings\Subscriber;

use WP_Rocket\Tests\Integration\AjaxTestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Subscriber::enable_mobile_cache
 * @uses   \WP_Rocket\Engine\Admin\Settings\Page::enable_mobile_cache
 * 
 * @group  AdminOnly
 */
class Test_EnableMobileCache extends AjaxTestCase {

    private static $admin_user_id  = 0;
	private static $editor_user_id = 0;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::installFresh();

		self::setAdminCap();

		//create an editor user that has the capability
		self::$admin_user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		//create an editor user that has no capability
		self::$editor_user_id = static::factory()->user->create( [ 'role' => 'editor' ] );
	}

    public static function tear_down_after_class()
	{
		parent::tear_down_after_class();
		self::uninstallAll();
	}

	public function set_up() {
		parent::set_up();

		$options = get_option( 'wp_rocket_settings', [] );
		$options['cache_mobile'] = 0;
		$options['do_caching_mobile_files'] = 0;
		update_option( 'wp_rocket_settings', $options );

        $this->action = 'rocket_enable_mobile_cache';
	}

	/**
	 * @dataProvider provideTestData
	 */
	public function testShouldEnableMobileCache( $is_user_auth ) {
		if ( $is_user_auth ) {
			$user_id = static::$admin_user_id;
		} else {
			$user_id = static::$editor_user_id;
		}

        wp_set_current_user( $user_id );
        
		$_POST['nonce'] = wp_create_nonce( 'rocket-ajax' );
        $_POST['action'] = 'rocket_enable_mobile_cache';
		$response       = $this->callAjaxAction();

		$options   = get_option( 'wp_rocket_settings' );
		$cache_mobile = $options['cache_mobile'];
		$do_caching_mobile_files = $options['do_caching_mobile_files'];

		if ( $is_user_auth ) {
			$this->assertTrue( $response->success );
			$this->assertEquals( 1, $cache_mobile );
			$this->assertEquals( 1, $do_caching_mobile_files );
		} else {
			$this->assertFalse( $response->success );
			$this->assertEquals( 0, $cache_mobile );
			$this->assertEquals( 0, $do_caching_mobile_files );
		}
	}

	public function provideTestData() {
		return $this->getTestData( __DIR__, 'enableMobileCache' );
	}
}
