<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Fixtures\i18n\i18nTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_clean_domain
 * @uses  ::get_rocket_i18n_home_url
 * @uses  ::get_rocket_i18n_to_preserve
 * @uses  ::get_rocket_i18n_uri
 * @uses  ::get_rocket_parse_url
 * @uses  ::rocket_get_constant
 * @uses  ::rocket_rrmdir
 *
 * @group Functions
 * @group Files
 * @group vfs
 */
class Test_RocketCleanDomain extends FilesystemTestCase {
	use i18nTrait;

	protected $path_to_test_data = '/inc/functions/rocketCleanDomain.php';
	private $urlsToClean;
	private $toPreserve;
	private $dirsToClean;

	public function setUp() {
		parent::setUp();

		Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_CACHE_PATH' )->andReturn( WP_ROCKET_CACHE_PATH );

		$this->urlsToClean = [];
		$this->toPreserve  = [];
		$this->dirsToClean = [];
	}

	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'] );
		remove_filter( 'rocket_clean_domain_urls', [ $this, 'checkRocketCleaDomainUrls' ], PHP_INT_MAX );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanSingleDomain( $i18n, $expected ) {
		$this->urlsToClean = $expected['rocket_clean_domain_urls'];
		$this->toPreserve  = $i18n['dirs_to_preserve'];
		$this->dirsToClean = $expected['cleaned'];

		$shouldNotClean = $this->getNonCleaned( $expected['non_cleaned'] );
		$this->setUpI18nPlugin( $i18n['lang'], $i18n );

		add_filter( 'rocket_clean_domain_urls', [ $this, 'checkRocketCleaDomainUrls' ], PHP_INT_MAX );

		// Run it.
		rocket_clean_domain( $i18n['lang'] );

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
		$actual = array_diff( $entriesAfterCleaning, $shouldNotClean );
		if ( ! empty( $expected['test_it'] ) ) {
			var_dump( $actual );
		} else {
			$this->assertEmpty( $actual );
		}
	}

	private function getNonCleaned( $config ) {
		$entries = [];
		foreach( $config as $entry => $scanDir ) {
			$entries[] = $entry;
			if ( $scanDir && $this->filesystem->is_dir( $entry ) ) {
				$entries = array_merge( $entries, $this->filesystem->getListing( $entry ) );
			}
		}
		return $entries;
	}

	public function checkRocketCleaDomainUrls( $urls ) {
		$this->assertSame( $this->urlsToClean, $urls );

		return $urls;
	}
}
