<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering ::rocket_mkdir_p
 * @uses  ::rocket_is_stream
 * @uses  ::rocket_direct_filesystem
 *
 * @group Functions
 * @group Files
 * @group vfs
 */
class Test_RocketMkdirP extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketMkdirP.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRecursivelyMkdirWhenDoesNotExist( $target, $should_mkdir, $new_path = '' ) {
		Functions\expect( 'rocket_is_stream' )
			->atLeast( )
			->with( $target )
			->andReturnUsing( function ( $path ) {
				$stream = substr( $path, 0, strpos( $path, '://' ) );

				return in_array( $stream, stream_get_wrappers(), true );

			} );

		if ( ! empty( $new_path ) ) {
			$this->assertFalse( $this->filesystem->exists( $new_path ) );
		}

		if ( $should_mkdir ) {
			$this->assertFalse( $this->filesystem->exists( $target ) );
		} else {
			$this->assertTrue( $this->filesystem->exists( $target ) );
		}

		$this->assertTrue( rocket_mkdir_p( $target ) );

		$dir = ! empty( $new_path ) ? $new_path : $target;
		$this->assertTrue( $this->filesystem->is_dir( $dir ) );
	}
}
