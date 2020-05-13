<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager;
use WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber;
use Mockery;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::update_user_token
 * @group  RocketCDN
 */
class Test_UpdateUserToken extends TestCase {
	private $data_manager;

	public function setUp() {
		parent::setUp();

		$this->data_manager = new DataManagerSubscriber(
			Mockery::mock( APIClient::class ),
			Mockery::mock( CDNOptionsManager::class )
		);

		Functions\when( 'check_ajax_referer' )->justReturn( true );
	}

	public function testShouldSendErrorWhenNoCapacity() {
		Functions\when( 'current_user_can' )->justReturn( false );
		Functions\expect( 'wp_send_json_error' )->once()->with( 'unauthorized_user' );

		$this->data_manager->update_user_token();
	}

	/**
	 * Test should delete the option and send "user_token_deleted" JSON success when the $_POST "value" is null.
	 */
	public function testShouldDeleteOptionAndSendUserTokenDeletedJSONSuccessWhenValueIsNull() {
		$_POST['value'] = null;

		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\expect( 'delete_option' )->once()->with( 'rocketcdn_user_token' );
		Functions\expect( 'wp_send_json_success' )->once()->with( 'user_token_deleted' );

		Functions\expect( 'wp_send_json_error' )->never();
		Functions\expect( 'sanitize_key' )->never();

		$this->data_manager->update_user_token();
	}

	/**
	 * Test should send "invalid_token_length" JSON error when the token value provided is not 40 characters length.
	 */
	public function testShouldSendInvalidTokenLengthJsonErrorWhenValueLengthIsNot40() {
		$_POST['value'] = 'not40charslong';

		$this->assertNotEquals( 40, strlen( $_POST['value'] ) );

		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\expect( 'sanitize_key' )->once()->with( 'not40charslong' )->andReturnFirstArg();
		Functions\expect( 'wp_send_json_error' )->once()->with( 'invalid_token_length' );
		Functions\expect( 'delete_option' )->never();
		Functions\expect( 'update_option' )->never();
		Functions\expect( 'wp_send_json_success' )->never();

		$this->data_manager->update_user_token();
	}

	/**
	 * Test should update the option and send "user_token_saved" JSON success when the token value is valid.
	 */
	public function testShouldUpdateOptionAndSendUserTokenSavedJsonSuccessWhenValueIsValid() {
		$_POST['value'] = '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b';

		$this->assertEquals( 40, strlen( $_POST['value'] ) );

		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\expect( 'sanitize_key' )->once()->with( $_POST['value'] )->andReturnFirstArg();
		Functions\expect( 'update_option' )->once()->with( 'rocketcdn_user_token', $_POST['value'] );
		Functions\expect( 'wp_send_json_success' )->once()->with( 'user_token_saved' );
		Functions\expect( 'wp_send_json_error' )->never();
		Functions\expect( 'delete_option' )->never();

		$this->data_manager->update_user_token();
	}
}
