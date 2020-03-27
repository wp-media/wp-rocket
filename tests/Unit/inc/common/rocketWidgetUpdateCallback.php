<?php

namespace WP_Rocket\Tests\Unit\inc\common;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers ::rocket_widget_update_callback
 * @uses  ::rocket_clean_domain
 * @group Common
 * @group Purge
 * @group vfs
 */
class Test_RocketWidgetUpdateCallback extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/common/rocketWidgetUpdateCallback.php';

	public function setUp() {
		parent::setUp();

		Functions\when( 'get_option' )->justReturn( '' );

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/common/purge.php';
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldInvokeRocketCleanDomainOnWidgetUpdate( $instance ) {
		Functions\expect( 'rocket_clean_domain' )->once()->andReturnNull();

		$this->assertSame( $instance, rocket_widget_update_callback( $instance ) );
	}
}
