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
class RocketCleanCacheThemeUpdateTest extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/common/rocketCleanCacheThemeUpdate.php';

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'upgrader_process_complete', 'rocket_clean_cache_theme_update' );
	}

	public function tear_down() {
		remove_filter( 'stylesheet', [ $this, 'set_current_theme_stylesheet' ] );
		remove_filter( 'template', [ $this, 'set_current_theme_stylesheet' ] );

		$this->restoreWpHook( 'upgrader_process_complete' );

		unset( $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'], $GLOBALS['debug_fs'] );

		parent::tear_down();
	}

	public function set_current_theme_stylesheet() {
		return 'default';
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanExpected( $hook_extra, $expected ) {
		if ( empty( $expected['cleaned'] ) ) {
			Functions\expect( 'rocket_clean_domain' )->never();
		}

		// Point the current theme at the updated one via filters (no DB writes, unlike switch_theme()).
		if ( ! empty( $expected['wp_get_theme'] ) ) {
			add_filter( 'stylesheet', [ $this, 'set_current_theme_stylesheet' ] );
			add_filter( 'template', [ $this, 'set_current_theme_stylesheet' ] );
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
