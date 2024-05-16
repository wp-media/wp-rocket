<?php

namespace WP_Rocket\Tests\Integration\inc\common;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering ::rocket_clean_cache_theme_update
 *
 * @uses ::rocket_clean_domain
 *
 * @group Common
 * @group Purge
 * @group vfs
 */
class Test_RocketCleanCacheThemeUpdate extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/common/rocketCleanCacheThemeUpdate.php';

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'upgrader_process_complete', 'rocket_clean_cache_theme_update');
	}

	public function tear_down() {
		$this->restoreWpHook( 'upgrader_process_complete' );

		unset( $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'], $GLOBALS['debug_fs'] );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanExpected( $hook_extra, $expected ) {
		if ( empty( $expected['cleaned'] ) ) {
			Functions\expect( 'rocket_clean_domain' )->never();
		}

		if ( isset( $expected['debug'] ) && $expected['debug'] ) {
			$GLOBALS['debug_fs'] = true;
		}

		$this->dumpResults = isset( $expected['dump_results'] ) ? $expected['dump_results'] : false;
		$this->generateEntriesShouldExistAfter( $expected['cleaned'] );

		// Update it.
		do_action( 'upgrader_process_complete', null, $hook_extra );

		$this->checkEntriesDeleted( $expected['cleaned'] );
		$this->checkShouldNotDeleteEntries();
	}
}
