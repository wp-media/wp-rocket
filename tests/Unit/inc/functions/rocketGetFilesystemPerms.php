<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering ::rocket_get_filesystem_perms
 *
 * @group  Files
 * @group  Functions
 */
class Test_RocketGetFilesystemPerms extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketGetFilesystemPerms.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnPerms( $type, $constant, $expected ) {
		$actual = rocket_get_filesystem_perms( $type );
		$this->assertSame( $expected, $actual );
	}
}
