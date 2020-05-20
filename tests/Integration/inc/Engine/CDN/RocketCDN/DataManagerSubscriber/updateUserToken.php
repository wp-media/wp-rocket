<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::update_user_token
 *
 * @group  AdminOnly
 * @group  DataManagerSubscriber
 * @group  RocketCDN
 */
class Test_UpdateUserToken extends AjaxTestCase {
	protected static $ajax_action = 'save_rocketcdn_token';

	public function testCallbackIsRegistered() {
		$this->assertCallbackRegistered( 'wp_ajax_save_rocketcdn_token', 'update_user_token' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldSendExpectedResponse( $config, $expected ) {
		$_POST['value'] = $config['post_value'];

		if ( false === $config['rocketcdn_user_token'] ) {
			$this->assertFalse( get_option( 'rocketcdn_user_token' ) );
		} elseif ( null === $config['rocketcdn_user_token'] ) {
			$this->assertNotEquals( 40, strlen( $_POST['value'] ) );
		} else {
			add_option( 'rocketcdn_user_token', $config['rocketcdn_user_token'] );
		}

		$response = $this->callAjaxAction();

		// Check the response.
		$this->assertSame( $expected['response']->success, $response->success );
		$this->assertEquals( $expected['response']->data, $response->data );

		$this->assertSame(
			$expected['rocketcdn_user_token'],
			get_option( 'rocketcdn_user_token' )
		);
	}
}
