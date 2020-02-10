<?php
namespace WP_Rocket\Tests\Unit\Functions;

use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers rocket_get_compressed_assets_rules()
 * @group Functions
 * @group Files
 */
class Test_RocketGetCompressedAssetsRules extends TestCase {
	protected function setUp() {
		parent::setUp();

		require_once( WP_ROCKET_PLUGIN_ROOT . 'inc/functions/htaccess.php' );
	}
}
