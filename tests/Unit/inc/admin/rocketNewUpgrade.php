<?php

namespace WP_Rocket\Tests\Unit\inc\admin;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::rocket_new_upgrade
 * @group admin
 * @group upgrade
 */
class Test_RocketNewUpgrade extends TestCase {
	public function setUp() {
		parent::setUp();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/admin/upgrader.php';
	}

	public function testShouldRegenerateAdvancedCacheFile() {
		Functions\when( 'rocket_is_ssl_website' )->justReturn( false );
		Functions\expect( 'rocket_generate_advanced_cache_file' )
			->once();
		Functions\expect( 'rocket_clean_cache_busting' )
			->once();

		rocket_new_upgrade( '3.5.1', '3.4.4' );
	}
}
