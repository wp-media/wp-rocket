<?php
namespace WP_Rocket\Tests\Unit\Functions;

use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers rocket_clean_minify()
 * @group Functions
 * @group htaccess
 */
class Test_RocketCleanMinify extends TestCase {
	protected function setUp() {
		parent::setUp();

		require( WP_ROCKET_PLUGIN_ROOT . 'inc/functions/files.php' );
	}
}
