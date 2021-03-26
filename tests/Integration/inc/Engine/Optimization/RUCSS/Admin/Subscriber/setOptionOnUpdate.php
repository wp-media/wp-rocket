<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::set_option_on_update
 *
 * @group  RUCSS
 * @group  AdminOnly
 */
class Test_SetOptionOnUpdate extends TestCase{
	public function setUp(): void {
		parent::setUp();

		$this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'set_option_on_update' );
	}

	public function tearDown() {
		parent::tearDown();

		$this->restoreWpFilter( 'wp_rocket_upgrade' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $old_version, $valid_version ) {
		do_action( 'wp_rocket_upgrade', '', $old_version );

		$options = get_option( 'wp_rocket_settings' );

		if ( $valid_version ) {
			$this->assertSame(
				0,
				$options['remove_unused_css']
			);
		} else {
			$this->assertSame(
				1,
				$options['remove_unused_css']
			);
		}
	}
}
