<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering ::rocket_is_stream
 * @group Functions
 * @group Files
 * @group vfs
 */
class Test_RocketIsStream extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketIsStream.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $path, $expected ) {
		 $this->assertSame( $expected, rocket_is_stream( $path ) );
	}
}
