<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;


/**
 * @covers ::rocket_find_wpconfig_path
 * @group Functions
 */
class Test_RocketFindWpconfigPath extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnValid( $config, $expected ) {

		$actual = rocket_find_wpconfig_path();
	}

}
