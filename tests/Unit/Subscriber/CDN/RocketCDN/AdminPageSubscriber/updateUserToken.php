<?php

namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber::update_user_token
 * @group  RocketCDN
 */
class Test_UpdateUserToken extends TestCase {
	private $page;

	public function setUp() {
		parent::setUp();

		$this->page = new AdminPageSubscriber(
			$this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' ),
			$this->createMock( 'WP_Rocket\Admin\Options_Data' ),
			$this->createMock( 'WP_Rocket\Admin\Settings\Beacon' ),
			'views/settings/rocketcdn'
		);

		Functions\when( 'check_ajax_referer' )->justReturn( true );
	}

	/**
	 * Test should delete the option and send "user_token_deleted" JSON success when the $_POST "value" is null.
	 */
	public function testShouldDeleteOptionAndSendUserTokenDeletedJSONSuccessWhenValueIsNull() {
		$_POST['value']  = null;

		Functions\expect( 'delete_option' )->once()->with( 'rocketcdn_user_token' );
		Functions\expect( 'wp_send_json_success' )->once()->with( 'user_token_deleted' );

		Functions\expect( 'wp_send_json_error' )->never();
		Functions\expect( 'sanitize_key' )->never();

		$this->page->update_user_token();
	}

	/**
	 * Test should send "invalid_token_length" JSON error when the token value provided is not 40 characters length.
	 */
	public function testShouldSendInvalidTokenLengthJsonErrorWhenValueLengthIsNot40() {
		$_POST['value']  = 'not40charslong';

		$this->assertNotEquals( 40, strlen( $_POST['value'] ) );

		Functions\expect( 'sanitize_key' )->once()->with( 'not40charslong' )->andReturnFirstArg();
		Functions\expect( 'wp_send_json_error' )->once()->with( 'invalid_token_length' );
		Functions\expect( 'delete_option' )->never();
		Functions\expect( 'update_option' )->never();
		Functions\expect( 'wp_send_json_success' )->never();

		$this->page->update_user_token();
	}

	/**
	 * Test should update the option and send "user_token_saved" JSON success when the token value is valid.
	 */
	public function testShouldUpdateOptionAndSendUserTokenSavedJsonSuccessWhenValueIsValid() {
		$_POST['value']  = '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b';

		$this->assertEquals( 40, strlen( $_POST['value'] ) );

		Functions\expect( 'sanitize_key' )->once()->with( $_POST['value'] )->andReturnFirstArg();
		Functions\expect( 'update_option' )->once()->with( 'rocketcdn_user_token', $_POST['value'] );
		Functions\expect( 'wp_send_json_success' )->once()->with( 'user_token_saved' );
		Functions\expect( 'wp_send_json_error' )->never();
		Functions\expect( 'delete_option' )->never();

		$this->page->update_user_token();
	}
}
