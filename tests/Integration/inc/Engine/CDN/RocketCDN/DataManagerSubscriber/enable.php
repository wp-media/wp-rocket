<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::enable
 *
 * @uses   \WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager::enable
 * @uses   \WP_Rocket\Admin\Options_Data::set
 * @uses   \WP_Rocket\Admin\Options::set
 * @uses   \WP_Rocket\Admin\Options::get_option_name
 * @uses   \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_subscription_data
 *
 * @group  AdminOnly
 * @group  DataManagerSubscriber
 * @group  RocketCDN
 */
class Test_Enable extends AjaxTestCase {
	protected static $ajax_action = 'rocketcdn_enable';

	public function tearDown() {
		parent::tearDown();

		delete_option( 'rocketcdn_process' );
		wp_clear_scheduled_hook( 'rocketcdn_check_subscription_status_event' );
	}

	public function testCallbackIsRegistered() {
		$this->assertCallbackRegistered( 'wp_ajax_rocketcdn_enable', 'enable' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedResponse( $config, $expected ) {
		$_POST['cdn_url'] = $config['cdn_url'];
		add_option( 'rocketcdn_process', true );

		// Run it.
		$response = $this->callAjaxAction();

		// Check the response.
		$this->assertSame( $expected['response']->success, $response->success );
		$this->assertEquals( $expected['response']->data, $response->data );

		// Check settings.
		if ( isset( $expected['settings'] ) ) {
			$this->assertSettings( $expected['settings'] );
		}
	}
}
