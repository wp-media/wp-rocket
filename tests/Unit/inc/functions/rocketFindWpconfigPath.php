<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;


/**
 * @covers ::rocket_find_wpconfig_path
 * @group Functions
 */
class Test_RocketFindWpconfigPath extends FilesystemTestCase {
	protected $path_to_test_data   = '/inc/functions/rocketFindWpconfigPath.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnValid( $config, $expected ) {

		$actual = rocket_find_wpconfig_path();
	}

}
