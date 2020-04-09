<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_rrmdir
 * @uses  ::rocket_direct_filesystem
 * @group Functions
 * @group Files
 * @group vfs
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

		$this->setUpOriginalEntries();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRecursivelyRemoveFilesAndDirectories( $to_delete, $to_preserve, $expected ) {
		$to_delete = $this->filesystem->getUrl( untrailingslashit( $to_delete ) );

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

		// Check the "deleted" files/directories no longer exist, i.e. were deleted.
		foreach ( $expected['deleted'] as $entry ) {
			$this->assertFalse( $this->filesystem->exists( $entry ) );
		}

		// Check the non-deleted files/directories still exist, i.e. were not deleted.
		$should_exist = array_diff( $this->original_entries, $expected['deleted'] );
		foreach ( $should_exist as $entry ) {
			$this->assertTrue( $this->filesystem->exists( $entry ) );
		}
	}
}
