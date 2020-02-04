<?php

namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Tests\Integration\AjaxTestCase;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber::disable
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_Disable extends AjaxTestCase {

	public function setUp() {
		parent::setUp();

		$_POST['nonce'] = wp_create_nonce( 'rocket-ajax' );
		$this->action   = 'rocketcdn_disable';
	}

	/**
	 * Test that the callback is registered to the action.
	 */
	public function testCallbackIsRegistered() {
		$this->assertTrue( has_action( 'wp_ajax_rocketcdn_disable' ) );

		global $wp_filter;
		$obj = $wp_filter['wp_ajax_rocketcdn_disable'];
		$callback_registration = current( $obj->callbacks[10] );
		$this->assertEquals( 'disable', $callback_registration['function'][1] );
	}

	public function testShouldSendJSONSuccess() {
		$_POST['action'] = 'rocketcdn_disable';

		add_option( 'rocketcdn_process', true );

		$response = $this->callAjaxAction();

		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertTrue( $response->success );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertEquals( 'rocketcdn_disabled', $response->data );
		$this->assertFalse( get_option( 'rocketcdn_process' ) );
		$this->assertFalse( wp_next_scheduled( 'rocketcdn_check_subscription_status_event' ) );

		$expected_subset = [
			'cdn'        => 0,
			'cdn_cnames' => [],
			'cdn_zone'   => [],
		];

		$options = get_option( 'wp_rocket_settings' );

		foreach ( $expected_subset as $key => $value ) {
			$this->assertArrayHasKey( $key, $options );
			$this->assertSame( $value, $options[$key] );
		}
	}
}
