<?php

namespace WP_Rocket\Tests\Unit\inc\common;

use Brain\Monkey\Functions;
use WP_Theme;

/**
 * Test class covering ::rocket_clean_cache_theme_update
 * @uses   ::rocket_clean_domain
 *
 * @group  Common
 * @group  Purge
 */
class Test_RocketCleanCacheThemeUpdate extends TestCase {
	protected $path_to_test_data = 'rocketCleanCacheThemeUpdate.php';

	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_Theme.php';
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $hook_extra, $expected ) {
		if ( empty( $expected['cleaned'] ) ) {
			Functions\expect( 'rocket_clean_domain' )->never();
		} else {
			Functions\expect( 'rocket_clean_domain' )->once()->andReturnNull();
		}

		if ( $expected['wp_get_theme'] ) {
			Functions\expect( 'wp_get_theme' )->once()->andReturnUsing(
				function () {
					return new WP_Theme( 'default', '/themes' );
				}
			);
		}

		rocket_clean_cache_theme_update( null, $hook_extra );
	}
}
