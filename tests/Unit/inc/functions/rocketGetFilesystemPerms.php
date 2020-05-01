<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers ::rocket_get_filesystem_perms
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
		if ( empty( $constant ) ) {
			Functions\expect( 'rocket_get_constant' )->with( 'ABSPATH' )->andReturn( 'vfs://public/' );
		} else {
			Functions\expect( 'rocket_get_constant' )->with( $constant, 0 )->andReturn( $expected );
		}

		$actual = rocket_get_filesystem_perms( $type );
		$this->assertSame( $expected, $actual );
	}
}
