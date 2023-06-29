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

	public function set_up() {
		parent::set_up();

		$options = get_option( 'wp_rocket_settings', [] );
		$options['cache_mobile'] = 0;
		$options['do_caching_mobile_files'] = 0;
		update_option( 'wp_rocket_settings', $options );
	}

	/**
	 * @dataProvider provideTestData
	 */
	public function testShouldEnableMobileCache( $is_user_auth ) {
		$this->action = 'rocket_enable_mobile_cache';

		if ( $is_user_auth ) {
			wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
		} else {
			wp_set_current_user( static::factory()->user->create( [ 'role' => 'editor' ] ) );
		}

		$_POST['nonce'] = wp_create_nonce( 'rocket-ajax' );
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
