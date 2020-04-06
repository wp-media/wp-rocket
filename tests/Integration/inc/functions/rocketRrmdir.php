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
	private $to_preserve;

	public function setUp() {
		parent::setUp();

		$this->to_preserve = [];
		$this->stats       = [
			'before_rocket_rrmdir' => did_action( 'before_rocket_rrmdir' ),
			'after_rocket_rrmdir'  => did_action( 'after_rocket_rrmdir' ),
		];

		$this->setUpOriginalEntries();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRecursivelyRemoveFilesAndDirectories( $to_delete, $to_preserve, $expected ) {
		$to_delete      = rtrim( $to_delete, '/\\' );
		$to_delete_path = $this->config['vfs_dir'] . $to_delete;
		$to_delete      = $this->filesystem->getUrl( $to_delete_path );
		$is_file        = $this->filesystem->is_file( $to_delete );
		$this->initPreserve( $to_preserve );

		// Run it.
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

		foreach ( $this->original_entries as $entry ) {
			if ( $is_file ) {
				$exists = $entry !== $to_delete_path;
				$this->assertSame( $exists, $this->filesystem->exists( $entry ) );
				continue;
			}

			$exists = ! (
				$this->startsWith( $entry, $to_delete_path )
				&&
				! $this->shouldPreserve( $entry )
			);

			$this->assertSame( $exists, $this->filesystem->exists( $entry ) );
		}
	}

	private function shouldPreserve( $entry ) {
		return (
			! empty( $to_preserve )
			&&
			in_array( $entry, $to_preserve, true )
		);
	}

	private function initPreserve( $to_preserve ) {
		if ( ! empty( $to_preserve ) ) {
			$this->to_preserve = array_map( [ $this, 'stripVfsRoot' ], $to_preserve );
		}
	}
}
