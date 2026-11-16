<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering ::get_rocket_option
 * @uses   \WP_Rocket\Admin\Options
 * @uses   \WP_Rocket\Admin\Options_Data
 *
 * @group  Options
 * @group  Functions
 */
class Test_GetRocketOption extends TestCase {

	/**
	 * Sets up an active RocketCDN subscription.
	 */
	public function set_up() {
		parent::set_up();

		// An active subscription keeps the CDN from being forced off by the licence check.
		set_transient( 'rocketcdn_status', [ 'subscription_status' => 'running' ] );
	}

	/**
	 * Removes the subscription transient.
	 */
	public function tear_down() {
		delete_transient( 'rocketcdn_status' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedOptionValue( $option, $default, $expected ) {
		$this->assertSame( $expected, get_rocket_option( $option, $default ) );
	}
}
