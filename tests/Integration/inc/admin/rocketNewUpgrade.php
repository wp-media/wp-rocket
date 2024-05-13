<?php

namespace WP_Rocket\Tests\Integration\inc\admin;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering ::rocket_new_upgrade
 *
 * @group admin
 * @group upgrade
 * @group AdminOnly
 */
class Test_RocketNewUpgrade extends TestCase {
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'rocket_new_upgrade' );
	}

	public function tear_down() {
		parent::tear_down();

		$this->restoreWpHook( 'wp_rocket_upgrade' );
	}

	public function testShouldRegenerateAdvancedCacheFile() {
		Functions\expect( 'rocket_generate_advanced_cache_file' )
			->atLeast()
			->times( 1 );

		do_action( 'wp_rocket_upgrade', '3.5.1', '3.4.4' );
	}
}
