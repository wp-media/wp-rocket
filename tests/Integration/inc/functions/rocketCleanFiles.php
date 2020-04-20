<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\GlobTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_clean_files
 * @uses  ::rocket_rrmdir
 * @uses  ::rocket_remove_url_protocol
 *
 * @group Functions
 * @group Files
 * @group vfs
 * @group thisone
 */
class Test_RocketCleanFiles extends FilesystemTestCase {
	use GlobTrait;

	protected $path_to_test_data = '/inc/functions/rocketCleanFiles.php';
	private   $dirsToClean;
	private   $stats             = [];

	public function setUp() {
		parent::setUp();

		Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_CACHE_PATH' )->andReturn( WP_ROCKET_CACHE_PATH );
		add_action( 'before_rocket_clean_file', [ $this, 'globHandler' ] );
		$this->dirsToClean = [];
		$this->stats       = [
			'before_rocket_clean_files' => did_action( 'before_rocket_clean_files' ),
			'before_rocket_clean_file'  => did_action( 'before_rocket_clean_file' ),
			'after_rocket_clean_file'   => did_action( 'after_rocket_clean_file' ),
			'after_rocket_clean_files'  => did_action( 'after_rocket_clean_files' ),
		];
	}

	public function tearDown() {
		parent::tearDown();

		remove_action( 'before_rocket_clean_file', [ $this, 'globHandler' ] );
		remove_filter( 'rocket_clean_domain_urls', [ $this, 'checkRocketCleaDomainUrls' ], PHP_INT_MAX );
	}

	public function globHandler() {
		foreach ( $this->dirsToClean as $dir ) {
			$this->deleteFiles( $dir, $this->filesystem );
		}
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanSingleDomain( $urls, $expected ) {
		$this->dirsToClean = $expected['cleaned'];

		$shouldNotClean = $this->getNonCleaned( $expected['non_cleaned'] );

		// Run it.
		rocket_clean_files( $urls );

		$number_of_urls = count( $urls );
		$this->assertEquals( 1, did_action( 'before_rocket_clean_files' ) - $this->stats['before_rocket_clean_files'] );
		$this->assertEquals( $number_of_urls, did_action( 'before_rocket_clean_file' ) - $this->stats['before_rocket_clean_file'] );
		$this->assertEquals( $number_of_urls, did_action( 'after_rocket_clean_file' ) - $this->stats['after_rocket_clean_file'] );
		$this->assertEquals( 1, did_action( 'after_rocket_clean_files' ) - $this->stats['after_rocket_clean_files'] );

		// Check the "cleaned" directories.
		foreach ( $expected['cleaned'] as $dir => $contents ) {
			// Deleted.
			if ( is_null( $contents ) ) {
				$this->assertFalse( $this->filesystem->exists( $dir ) );
			} else {
				$shouldNotClean[] = trailingslashit( $dir );
				// Emptied, but not deleted.
				$this->assertSame( $contents, $this->filesystem->getFilesListing( $dir ) );
			}
		}

		// Check the non-cleaned files/directories still exist.
		$entriesAfterCleaning = $this->filesystem->getListing( $this->filesystem->getUrl( $this->config['vfs_dir'] ) );
		$actual               = array_diff( $entriesAfterCleaning, $shouldNotClean );
		$this->assertEmpty( $actual );
	}

	private function getNonCleaned( $config ) {
		$entries = [];
		foreach ( $config as $entry => $scanDir ) {
			$entries[] = $entry;
			if ( $scanDir && $this->filesystem->is_dir( $entry ) ) {
				$entries = array_merge( $entries, $this->filesystem->getListing( $entry ) );
			}
		}

		return $entries;
	}
}
