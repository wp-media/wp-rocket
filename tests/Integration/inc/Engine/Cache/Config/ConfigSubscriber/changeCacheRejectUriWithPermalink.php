<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\Config\ConfigSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\Config\ConfigSubscriber::change_cache_reject_uri_with_permalink
 *
 * @group Config
 */
class Test_ChangeCacheRejectUriWithPermalink extends TestCase {
	public function set_up() {
		parent::set_up();

		// Install the preload cache table to prevent DB error caused by permalink changed.
		self::installPreloadCacheTable();

		// Disable ATF optimization to prevent DB request (unrelated to the test).
		add_filter( 'rocket_above_the_fold_optimization', '__return_false' );
	}

	public function tear_down() {
		// Uninstall the preload cache table.
		self::uninstallPreloadCacheTable();

		// Re-enable ATF optimization.
		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {

		if ( isset( $config['permalink'] ) ) {
			$this->set_permalink_structure( $config['permalink']['structure'] );
		}

		$this->assertSame(
			$expected,
			apply_filters( 'pre_update_option_wp_rocket_settings', $config['value'],  $config['old_value'] )
		);
	}
}
