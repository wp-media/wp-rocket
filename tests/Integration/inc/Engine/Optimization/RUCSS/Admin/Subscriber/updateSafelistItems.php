<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::update_safelist_items
 *
 * @group RUCSS
 * @group AdminOnly
 */
class Test_UpdateSafelistItems extends TestCase {
	public function set_up() {
		parent::set_up();

		/**
		 * Temporarily install Used CSS table to avoid error in test run
		 *
		 * Updating remove_unused_css_safelist causes another callback to be executed,
		 * which requires the table to be installed:
		 * update_option_wp_rocket_settings => clean_used_css_and_cache()
		 */
		self::installUsedCssTable();

		$this->setUpSettings();
		$this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'update_safelist_items', 15 );
	}

	public function tear_down() {
		// Delete the table after the test run.
		self::uninstallUsedCssTable();

		$this->tearDownSettings();
		$this->restoreWpHook( 'wp_rocket_upgrade' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->mergeExistingSettingsAndUpdate( $config['options'] );

		do_action( 'wp_rocket_upgrade', '', $config['version'] );

		$updated = get_option( 'wp_rocket_settings' );

		if ( $expected ) {
			$this->assertSame(
				$expected['remove_unused_css_safelist'],
				$updated['remove_unused_css_safelist']
			);
		} else {
			$this->assertIsArray( $updated );
		}
	}
}
