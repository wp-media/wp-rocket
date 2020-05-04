<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use WPMedia\PHPUnit\Integration\AjaxTestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::update_user_token
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_UpdateUserToken extends AjaxTestCase {
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
		$this->action   = 'save_rocketcdn_token';
	}

	/**
	 * Test that the callback is registered to the action.
	 */
	public function testCallbackIsRegistered() {
		$this->assertTrue( has_action( 'wp_ajax_save_rocketcdn_token' ) );

		global $wp_filter;
		$obj = $wp_filter['wp_ajax_save_rocketcdn_token'];
		$callback_registration = current( $obj->callbacks[10] );
		$this->assertEquals( 'update_user_token', $callback_registration['function'][1] );
	}

	/**
	 * Test should delete the option and send "user_token_deleted" JSON success when the $_POST "value" is null.
	 */
	public function testShouldDeleteOptionAndSendUserTokenDeletedJSONSuccessWhenValueIsNull() {
		add_option( 'rocketcdn_user_token', '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b' );

		$_POST['action'] = 'save_rocketcdn_token';
		$_POST['value']  = null;


		$response = $this->callAjaxAction();

		$this->assertFalse( get_option( 'rocketcdn_user_token' ) );
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertTrue( $response->success );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertEquals( 'user_token_deleted', $response->data );
	}

	/**
	 * Test should send "invalid_token_length" JSON error when the token value provided is not 40 characters length.
	 */
	public function testShouldSendInvalidTokenLengthJsonErrorWhenValueLengthIsNot40() {
		$_POST['value'] = 'not40charslong';
		$this->assertNotEquals( 40, strlen( $_POST['value'] ) );

		$response = $this->callAjaxAction();

		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertFalse( $response->success );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertEquals( 'invalid_token_length', $response->data );
	}

	/**
	 * Test should update the option and send "user_token_saved" JSON success when the token value is valid.
	 */
	public function testShouldUpdateOptionAndSendUserTokenSavedJsonSuccessWhenValueIsValid() {
		$this->assertFalse( get_option( 'rocketcdn_user_token' ) );
		$_POST['value'] = '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b';
		$this->assertEquals( 40, strlen( $_POST['value'] ) );

		$response = $this->callAjaxAction();

		// Check the response.
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertTrue( $response->success );
		$this->assertObjectHasAttribute( 'data', $response );
		$this->assertEquals( 'user_token_saved', $response->data );

		// Check that the option was updated.
		$this->assertSame( $_POST['value'], get_option( 'rocketcdn_user_token' ) );
	}
}
