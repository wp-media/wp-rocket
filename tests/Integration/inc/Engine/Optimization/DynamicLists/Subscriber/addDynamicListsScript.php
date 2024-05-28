<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DynamicLists\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DynamicLists\Subscriber::add_dynamic_lists_script
 *
 * @group DynamicLists
 */
class Test_AddDynamicListsScripts extends TestCase {
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

	public function testShouldReturnExpected() {
		$this->set_permalink_structure( "/%postname%/" );

		$result = apply_filters( 'rocket_localize_admin_script', [] );

		$this->assertArrayHasKey( 'rest_url', $result );
		$this->assertArrayHasKey( 'rest_nonce', $result );
		$this->assertContains( 'http://example.org/wp-json/wp-rocket/v1/dynamic_lists/update/', $result );
	}
}
