<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use PLL_Frontend;
use SitePress;
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
	protected $path_to_test_data = '/inc/functions/rocketCleanDomain.php';
	private $urlsToClean;
	private $toPreserve;
	private $dirsToClean;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/SitePress.php';
		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/i18n/PLL_Frontend.php';
	}

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
		remove_filter( 'rocket_clean_domain_urls', [ $this, 'checkRocketCleaDomainUrls' ], PHP_INT_MIN );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanSingleDomain( $i18n, $expected ) {
		$this->urlsToClean = $expected['rocket_clean_domain_urls'];
		$this->toPreserve  = $i18n['get_rocket_i18n_to_preserve'];
		$this->dirsToClean = $expected['cleaned'];

		$shouldNotClean = $this->getNonCleaned( $expected['non_cleaned'] );
		$this->setUpI18nPlugin( $i18n['lang'], $i18n );

		add_filter( 'rocket_clean_domain_urls', [ $this, 'checkRocketCleaDomainUrls' ], PHP_INT_MIN );

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
		$this->assertEmpty( array_diff( $entriesAfterCleaning, $shouldNotClean ) );
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

	private function setUpI18nPlugin( $lang, $config ) {
		$home_url = home_url();
		$data     = array_merge(
			[
				'codes' => [],
				'langs' => [],
				'uris'  => [],
			],
			$config['data']
		);
		$langs    = $data['langs'];

		switch ( $config['i18n_plugin'] ) {
			case 'wpml':
				$GLOBALS['sitepress']                   = new SitePress();
				$GLOBALS['sitepress']->active_languages = $data['codes'];
				$GLOBALS['sitepress']->home_root        = $home_url;
				$GLOBALS['sitepress']->uris_config      = $data['uris'];
				break;

			case 'qtranslate':
			case 'qtranslate-x':
				$GLOBALS['q_config'] = [ 'enabled_languages' => $langs ];

				if ( empty( $lang ) || empty( $langs ) ) {
					Functions\expect( 'qtranxf_convertURL' )->with( $home_url, $lang, true )->never();
					Functions\expect( 'qtrans_convertURL' )->with( $home_url, $lang, true )->never();

					return;
				}


				Functions\expect( 'qtranxf_convertURL' )
//					->atLeast( 1 )
					->with( $home_url, $lang, true )
					->andReturnUsing( function ( $home_url, $lang ) use ( $langs ) {
						if ( empty( $lang ) ) {
							return $home_url;
						}

						if ( empty( $langs ) ) {
							return $home_url;
						}

						return trailingslashit( $home_url ) . $lang;
					} );
				break;

			case 'polylang':
				if ( ! empty( $langs ) ) {
					$GLOBALS['polylang'] = new PLL_Frontend( $data['options'] );
					Functions\expect( 'PLL' )->andReturn( $GLOBALS['polylang'] );
					Functions\expect( 'pll_home_url' )
						->with( $lang )
						->andReturnUsing( function ( $lang ) use ( $langs, $home_url ) {
							if ( empty( $lang ) ) {
								return $home_url;
							}

							if ( in_array( $lang, $langs, true ) ) {
								return trailingslashit( $home_url ) . $lang;
							}

							return $home_url;
						} );
				} else {
					$GLOBALS['polylang'] = 'not-empty';
					Functions\expect( 'PLL' )->never();
				}

				Functions\expect( 'pll_languages_list' )->andReturn( $langs );
				break;

			default:
				Functions\expect( 'get_rocket_i18n_code' )->never();
		}
	}
}
