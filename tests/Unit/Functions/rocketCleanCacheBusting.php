<?php
namespace WP_Rocket\Tests\Unit\Functions;

use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers rocket_clean_cache_busting()
 * @group Functions
 * @group Files
 */
class Test_RocketCleanCacheBusting extends TestCase {
	protected function setUp() {
		parent::setUp();

		require_once( WP_ROCKET_PLUGIN_ROOT . 'inc/functions/files.php' );
	}
}
