<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering ::rocket_put_content
 * @uses  ::rocket_get_filesystem_perms
 * @uses  ::rocket_direct_filesystem
 *
 * @group Functions
 * @group Files
 * @group vfs
 */
class Test_RocketPutContent extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketPutContent.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldPutContent( $file, $content ) {
		Functions\expect( 'rocket_get_filesystem_perms' )
			->once()
			->with( 'file' )
			->andReturn( 0666 );
		$original_content = $this->filesystem->get_contents( $file );

		$this->assertTrue( rocket_put_content( $file, $content ) );

		// Check that the file exists.
		$this->assertTrue( $this->filesystem->exists( $file ) );

		// Check the content.
		$new_content = $this->filesystem->get_contents( $file );
		$this->assertNotSame( $original_content, $new_content );
		$this->assertSame( $content, $new_content );
	}
}
