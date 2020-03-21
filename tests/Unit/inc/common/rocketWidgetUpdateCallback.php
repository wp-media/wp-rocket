<?php

namespace WP_Rocket\Tests\Unit\inc\common;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::rocket_widget_update_callback
 * @group Common
 * @group Purge
 */
class Test_RocketWidgetUpdateCallback extends TestCase {

	protected function setUp() {
		parent::setUp();

		Functions\when( 'get_option' )->justReturn( '' );

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/common/purge.php';
	}

	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldInvokeRocketCleanDomainOnWidgetUpdate( $instance ) {
		Functions\expect( 'rocket_clean_domain' )->once()->andReturnNull();

		$this->assertSame( $instance, rocket_widget_update_callback( $instance ) );
	}

	public function addDataProvider() {
		return $this->getTestData( __DIR__, 'rocketWidgetUpdateCallback' );
	}
}
