<?php

namespace WP_Rocket\Tests\Integration\inc\common;

use WP_Widget_Text;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering ::rocket_widget_update_callback
 * @uses   ::rocket_clean_domain
 *
 * @group  Common
 * @group  Purge
 * @group  vfs
 */
class Test_RocketWidgetUpdateCallback extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/common/rocketWidgetUpdateCallback.php';

	public static function wpSetUpBeforeClass( $factory ) {
		wp_set_current_user( $factory->user->create( [ 'role' => 'administrator' ] ) );
	}

	public function testCallbackIsRegistered() {
		$this->assertEquals( 10, has_filter( 'widget_update_callback', 'rocket_widget_update_callback' ) );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldInvokeRocketCleanDomainOnWidgetUpdate( $instance, $expected ) {
		$widget                             = new WP_Widget_Text();
		$_POST["widget-{$widget->id_base}"] = $instance;

		$this->dumpResults = isset( $expected['dump_results'] ) ? $expected['dump_results'] : false;
		$this->generateEntriesShouldExistAfter( $expected['cleaned'] );

		// Update it.
		$widget->update_callback();

		$this->checkEntriesDeleted( $expected['cleaned'] );
		$this->checkShouldNotDeleteEntries();
	}
}
