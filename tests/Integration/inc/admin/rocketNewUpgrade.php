<?php

namespace WP_Rocket\Tests\Integration\Inc\admin;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers ::rocket_new_upgrade
 * @group admin
 * @group upgrade
 * @group AdminOnly
 */
class Test_RocketNewUpgrade extends TestCase {

	public function testShouldRegenerateAdvancedCacheFile() {
		Functions\expect( 'rocket_generate_advanced_cache_file' )
			->atLeast()
			->times( 1 );

		do_action( 'wp_rocket_upgrade', '3.5.1', '3.4.4' );
	}
}
