<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_rrmdir
 * @uses  ::rocket_direct_filesystem
 * @group Functions
 * @group Files
 * @group vfs
 * @group thisone
 */
class Test_RocketRrmdir extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketRrmdir.php';
	private $stats;

	public function setUp() {
		parent::setUp();

		$this->stats = [
			'before_rocket_rrmdir' => did_action( 'before_rocket_rrmdir' ),
			'after_rocket_rrmdir'  => did_action( 'after_rocket_rrmdir' ),
		];
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRecursivelyRemoveFilesAndDirectories( $to_delete, $to_preserve, $expected ) {
		$to_delete = $this->filesystem->getUrl( $this->config['vfs_dir'] . $to_delete );

		rocket_rrmdir( $to_delete, $to_preserve );

		// Check the action events.
		$this->assertEquals(
			$expected['before_rocket_rrmdir'],
			did_action( 'before_rocket_rrmdir' ) - $this->stats['before_rocket_rrmdir']
		);
		$this->assertEquals(
			$expected['after_rocket_rrmdir'],
			did_action( 'after_rocket_rrmdir' ) - $this->stats['after_rocket_rrmdir']
		);

		// Check that the expected files/directories were actually deleted.
		foreach ( $expected['deleted'] as $path ) {
			$this->assertFalse( $this->filesystem->exists( $this->config['vfs_dir'] . $path ) );
		}

		if ( empty( $to_preserve ) ) {
			return;
		}

		foreach( $this->getOriginalPreservedFiles( $to_preserve ) as $path ) {
			$this->assertTrue( $this->filesystem->exists( $path ) );
		}
	}

	private function getOriginalPreservedFiles( $to_preserve ) {
		$preserves = [];
		foreach( $to_preserve as $dir ) {
			$preserves[] = str_replace( 'vfs://public/', '', $dir );
		}

		$preserve_files = [];
		foreach( $this->original_files as $file ) {
			foreach( $preserves as $dir ) {
				if ( substr( $file, 0, strlen( $dir ) ) === $dir ) {
					$preserve_files[] = $file;
				}
			}
		}

		return $preserve_files;
	}
}
