<?php

namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber::update_user_token
 * @group  RocketCDN
 */
class Test_UpdateUserToken extends TestCase {
	private $api_client;
	private $options;
	private $beacon;

	public function setUp() {
		parent::setUp();

		$this->api_client = $this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' );
		$this->options    = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$this->beacon     = $this->createMock( 'WP_Rocket\Admin\Settings\Beacon' );
	}

	/**
	 * Test should return null when $_POST values are not set
	 */
	public function testShouldReturnNullWhenPOSTNotSet() {
		Functions\when( 'check_ajax_referer' )->justReturn( true );

		$page = new AdminPageSubscriber( $this->api_client, $this->options, $this->beacon, 'views/settings/rocketcdn' );

		$this->assertNull( $page->update_user_token() );
	}

	/**
	 * Test should return null when the $_POST action is incorrect
	 */
	public function testShouldReturnNullWhenInvalidPOSTAction() {
		Functions\when( 'check_ajax_referer' )->justReturn( true );

		$_POST['action'] = 'invalid';
		$_POST['value']  = 'test';

		$page = new AdminPageSubscriber( $this->api_client, $this->options, $this->beacon, 'views/settings/rocketcdn' );

		$this->assertNull( $page->update_user_token() );
	}

	/**
	 * Test should delete the option when the value is null
	 */
	public function testShouldDeleteOptionWhenValueIsNull() {
		Functions\when( 'check_ajax_referer' )->justReturn( true );

		$_POST['action'] = 'save_rocketcdn_token';
		$_POST['value']  = null;

		Functions\expect( 'delete_option' )
			->once()
			->with( 'rocketcdn_user_token' );

		$page = new AdminPageSubscriber( $this->api_client, $this->options, $this->beacon, 'views/settings/rocketcdn' );

		$page->update_user_token();
	}

	/**
	 * Test should return null when the value provided is not 40 characters length
	 */
	public function testShouldReturnNullWhenValueLengthIsNot40() {
		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'sanitize_key' )->returnArg();

		$_POST['action'] = 'save_rocketcdn_token';
		$_POST['value']  = 'test';

		$page = new AdminPageSubscriber( $this->api_client, $this->options, $this->beacon, 'views/settings/rocketcdn' );

		$this->assertNull( $page->update_user_token() );
	}

	/**
	 * Test should update the option when the value is valid
	 */
	public function testShouldUpdateOptionWhenValueIsValid() {
		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'sanitize_key' )->returnArg();

		$_POST['action'] = 'save_rocketcdn_token';
		$_POST['value']  = '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b';

		Functions\expect( 'update_option' )
			->once()
			->with( 'rocketcdn_user_token', '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b' );

		$page = new AdminPageSubscriber( $this->api_client, $this->options, $this->beacon, 'views/settings/rocketcdn' );

		$page->update_user_token();
	}
}
