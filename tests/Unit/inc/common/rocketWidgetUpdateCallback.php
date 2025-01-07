<?php

namespace WP_Rocket\Tests\Unit\inc\common;

use Brain\Monkey\Functions;

/**
 * Test class covering ::rocket_widget_update_callback
 * @uses   ::rocket_clean_domain
 *
 * @group  Common
 * @group  Purge
 */
class Test_RocketWidgetUpdateCallback extends TestCase {
	protected $path_to_test_data = 'rocketWidgetUpdateCallback.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldInvokeRocketCleanDomainOnWidgetUpdate( $instance ) {
		Functions\expect( 'rocket_clean_domain' )->once()->andReturnNull();

		$this->assertSame( $instance, rocket_widget_update_callback( $instance ) );
	}
}
