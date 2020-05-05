<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use WPMedia\PHPUnit\Integration\AjaxTestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::enable
 *
 * @uses \WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager::enable
 * @uses \WP_Rocket\Admin\Options_Data::set
 * @uses \WP_Rocket\Admin\Options::set
 * @uses \WP_Rocket\Admin\Options::get_option_name
 * @uses \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_subscription_data
 *
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_Enable extends AjaxTestCase {
	/**
	 * User's ID.
	 * @var int
	 */
	private static $user_id = 0;

	/**
	 * Set up the User ID before tests start.
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public function setUp() {
		parent::setUp();

		wp_set_current_user( self::$user_id );
		$_POST['nonce'] = wp_create_nonce( 'rocket-ajax' );
		$this->action   = 'rocketcdn_enable';
	}

	public function tearDown() {
		parent::tearDown();

		delete_option( 'rocketcdn_process' );
		wp_clear_scheduled_hook( 'rocketcdn_check_subscription_status_event' );
	}

	/**
	 * Test that the callback is registered to the action.
	 */
	public function testCallbackIsRegistered() {
		$this->assertTrue( has_action( 'wp_ajax_rocketcdn_enable' ) );

		global $wp_filter;
		$obj = $wp_filter['wp_ajax_rocketcdn_enable'];
		$callback_registration = current( $obj->callbacks[10] );
		$this->assertEquals( 'enable', $callback_registration['function'][1] );
	}

	public function testShouldSendErrorWhenCDNURLEmpty() {
		$_POST['action'] = 'rocketcdn_enable';
		$_POST['cdn_url']  = null;

		$expected_data = (object) [
			'process' => 'subscribe',
			'message' => 'cdn_url_empty',
		];

		$response = $this->callAjaxAction();

		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertFalse( $response->success );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertEquals( $expected_data, $response->data );
	}

	public function testShouldSendErrorWhenCDNURLInvalid() {
		$_POST['action'] = 'rocketcdn_enable';
		$_POST['cdn_url']  = '%20%20';

		$expected_data = (object) [
			'process' => 'subscribe',
			'message' => 'cdn_url_invalid_format',
		];

		$response = $this->callAjaxAction();

		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertFalse( $response->success );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertEquals( $expected_data, $response->data );
	}

	public function testShouldSendSuccessWhenCDNURLValid() {
		$_POST['action'] = 'rocketcdn_enable';
		$_POST['cdn_url']  = 'https://rocketcdn.me';

		$expected_data = (object) [
			'process' => 'subscribe',
			'message' => 'rocketcdn_enabled',
		];

		add_option( 'rocketcdn_process', true );

		$response = $this->callAjaxAction();

		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertTrue( $response->success );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertEquals( $expected_data, $response->data );
		$this->assertFalse( get_option( 'rocketcdn_process' ) );
		$this->assertNotFalse( wp_next_scheduled( 'rocketcdn_check_subscription_status_event' ) );

		$expected_subset = [
			'cdn'        => 1,
			'cdn_cnames' => [ 'https://rocketcdn.me' ],
			'cdn_zone'   => [ 'all' ],
		];

		$options = get_option( 'wp_rocket_settings' );

		foreach ( $expected_subset as $key => $value ) {
			$this->assertArrayHasKey( $key, $options );
			$this->assertSame( $value, $options[$key] );
		}
	}
}
