<?php

namespace WP_Rocket\Tests\Unit\inc\admin;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers ::rocket_new_upgrade
 * @group admin
 * @group upgrade
 */
class Test_RocketNewUpgrade extends TestCase {
	public function setUp() : void {
		parent::setUp();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/admin/upgrader.php';
	}

	public function testShouldRegenerateAdvancedCacheFile() {
		Functions\when( 'rocket_is_ssl_website' )->justReturn( false );
		Functions\expect( 'rocket_generate_advanced_cache_file' )
			->once();
		Functions\expect( 'rocket_clean_cache_busting' )
			->once();
		Functions\expect( 'rocket_clean_domain' )
			->once();
		Functions\expect( 'rocket_generate_config_file' )
			->once();
		Functions\expect( 'rocket_clean_minify' )
			->with( 'css' )
			->once();
		Functions\expect( 'rocket_get_constant' )
			->with( 'WP_ROCKET_SLUG' )
			->andReturn( 'wp_rocket_settings' );
		Functions\expect( 'get_option' )
			->twice()
			->andReturn( [] );
		Functions\expect( 'update_option' )
			->once();
		Functions\when( 'wp_clear_scheduled_hook' )->justReturn( 1 );
		Functions\when( 'rocket_rrmdir' )->justReturn( 1 );
		Functions\expect( 'delete_transient' )
			->once()->with( 'wp_rocket_pricing' );
		rocket_new_upgrade( '3.7', '3.4.4' );
	}
}
