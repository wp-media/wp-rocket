<?php

namespace WP_Rocket\Tests\Integration\inc\API;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers ::rocket_bypass
 * @group API
 */
class Bypass extends TestCase {
	public static function set_up_before_class() {
		parent::set_up_before_class();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/API/bypass.php';
	}

	public function tear_down() {
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $query_vars, $expected ) {
		$_GET = $query_vars;

		if ( $expected ) {
			$this->assertTrue( rocket_bypass() );
		} else {
			$this->assertFalse( rocket_bypass() );
		}
	}
}
