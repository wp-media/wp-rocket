<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\CSS\AdminSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\Optimization\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber::clean_minify
 *
 * @uses ::rocket_clean_minify
 * @uses ::rocket_direct_filesystem
 *
 * @group Optimize
 * @group Minify
 * @group AdminSubscriber
 * @group AdminOnly
 */
class Test_CleanMinify extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/Minify/CSS/AdminSubscriber/cleanMinify.php';

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'update_option_wp_rocket_settings', 'clean_minify' );
	}

	public function tear_down() {
		parent::tear_down();

		$this->restoreWpHook( 'update_option_wp_rocket_settings' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testCleanMinify( $settings, $expected ) {
		$this->dumpResults = isset( $expected['dump_results'] ) ? $expected['dump_results'] : false;
		$this->generateEntriesShouldExistAfter( $expected['cleaned'] );

		// Run it.
		$this->mergeExistingSettingsAndUpdate( $settings );

		$this->checkEntriesDeleted( $expected['cleaned'] );
		$this->checkShouldNotDeleteEntries();
	}
}
