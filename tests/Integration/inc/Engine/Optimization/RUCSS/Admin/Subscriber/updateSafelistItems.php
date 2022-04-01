<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::update_safelist_items
 *
 * @group  RUCSS
 * @group  AdminOnly
 */
class Test_UpdateSafelistItems extends TestCase {
	public function setUp(): void {
		parent::setUp();

		$this->setUpSettings();
		$this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'update_safelist_items', 15 );
	}

	public function tearDown() {
		parent::tearDown();

		$this->tearDownSettings();
		$this->restoreWpFilter( 'wp_rocket_upgrade' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->mergeExistingSettingsAndUpdate( $config['options'] );

		do_action( 'wp_rocket_upgrade', '', $config['old_version'] );

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
