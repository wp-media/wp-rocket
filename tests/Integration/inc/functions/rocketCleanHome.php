<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_clean_home()
 * @group Functions
 */
class Test_RocketCleanHome extends FilesystemTestCase {
	protected $path_to_test_data   = '/inc/functions/rocketCleanHome.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanHome( $config, $expected ) {

		$actual = rocket_clean_home();
	}

}
