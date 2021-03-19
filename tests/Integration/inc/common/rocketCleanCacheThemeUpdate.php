<?php

namespace WP_Rocket\Tests\Integration\inc\common;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_clean_cache_theme_update
 * @uses   ::rocket_clean_domain
 *
 * @group  Common
 * @group  Purge
 * @group  vfs
 */
class Test_RocketCleanCacheThemeUpdate extends FilesystemTestCase {
	protected        $path_to_test_data = '/inc/common/rocketCleanCacheThemeUpdate.php';
	protected static $hooks;

	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

		self::$hooks = $GLOBALS['wp_filter']['upgrader_process_complete']->callbacks;
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		$GLOBALS['wp_filter']['upgrader_process_complete']->callbacks = self::$hooks;
	}

	public function setUp() : void {
		parent::setUp();

		// Unregister all of the callbacks registered to the action event for these tests.
		$GLOBALS['wp_filter']['upgrader_process_complete']->callbacks = [];
		add_action( 'upgrader_process_complete', 'rocket_clean_cache_theme_update', 10, 2 );
	}

	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'] );
		unset( $GLOBALS['debug_fs'] );
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
