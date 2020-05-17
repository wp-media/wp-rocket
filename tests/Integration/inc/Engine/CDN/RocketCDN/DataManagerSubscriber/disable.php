<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::disable
 *
 * @uses \WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager::disable
 * @uses \WP_Rocket\Admin\Options_Data::set
 * @uses \WP_Rocket\Admin\Options::set
 * @uses \WP_Rocket\Admin\Options::get_option_name
 *
 * @group  AdminOnly
 * @group  DataManagerSubscriber
 * @group  RocketCDN
 */
class Test_Disable extends AjaxTestCase {
	protected static $ajax_action = 'rocketcdn_disable';

	public function testCallbackIsRegistered() {
		$this->assertTrue( has_action( 'wp_ajax_rocketcdn_disable' ) );

		global $wp_filter;
		$obj = $wp_filter['wp_ajax_rocketcdn_disable'];
		$callback_registration = current( $obj->callbacks[10] );
		$this->assertEquals( 'disable', $callback_registration['function'][1] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedResponse( $expected ) {
		add_option( 'rocketcdn_process', true );

		// Run it.
		$response = $this->callAjaxAction();

		// Check the response.
		$this->assertSame( $expected['response']->success, $response->success );
		$this->assertEquals( $expected['response']->data, $response->data );

		$this->assertEquals( $expected['rocketcdn_process'], get_option( 'rocketcdn_process' ) );
		$this->assertEquals( $expected['cron_is_scheduled'], wp_next_scheduled( 'rocketcdn_check_subscription_status_event' ) );

		// Check settings.
		if ( isset( $expected['settings'] ) ) {
			$this->assertSettings( $expected['settings'] );
		}
	}
}
