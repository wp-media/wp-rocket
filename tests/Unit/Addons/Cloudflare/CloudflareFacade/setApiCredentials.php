<?php

namespace WP_Rocket\Tests\Unit\Addons\Cloudflare\CloudflareFacade;

use Brain\Monkey\Functions;
use Cloudflare\Api;
use Mockery;
use WP_Rocket\Addons\Cloudflare\CloudflareFacade;
use WP_Rocket\Addons\Cloudflare\Imagify_Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers WP_Rocket\Addons\Cloudflare\CloudflareFacade::set_api_credentials
 *
 * @group  Cloudflare
 */
class Test_SetApiCredentials extends TestCase {

	protected function setUp() {
		parent::setUp();

		Functions\when( 'rocket_get_constant' )->justReturn( '3.5' );
	}

	protected function getApi( $api_mock, $skip_method_mock = false ) {
		if ( $skip_method_mock ) {
			return new CloudflareFacade( $api_mock );
		}

		$mock = Mockery::mock( 'WP_Rocket\Addons\Cloudflare\CloudflareFacade[init_api_objects]', [ $api_mock ] )->shouldAllowMockingProtectedMethods();
		$mock->shouldReceive( 'init_api_objects' )->andReturnNull();
		return $mock;
	}

	/**
	 * Test should set the email on the API.
	 */
	public function testShouldSetEmail() {
		$api_mock = Mockery::mock( Api::class, [
			'setAuthKey'    => null,
			'setCurlOption' => null,
		] );
		$api      = $this->getApi( $api_mock );

		$api_mock->shouldReceive( 'setEmail' )->with( null );
		$api->set_api_credentials( null, null, null );

		$api_mock->shouldReceive( 'setEmail' )->with( 'test@example.com' );
		$api->set_api_credentials( 'test@example.com', null, null );
	}

	/**
	 * Test should set the API key on the API.
	 */
	public function testShouldSetApiKeyWhenGiven() {
		$api_mock = Mockery::mock( Api::class, [
			'setEmail'      => null,
			'setCurlOption' => null,
		] );
		$api      = $this->getApi( $api_mock );

		$api_mock->shouldReceive( 'setAuthKey' )->with( null );
		$api->set_api_credentials( null, null, null );

		$api_mock->shouldReceive( 'setAuthKey' )->with( 'API_KEY' );
		$api->set_api_credentials( 'test@example.com', 'API_KEY', null );
	}

	/**
	 * Test should set the curl option with the current version Rocket.
	 */
	public function testShouldSetCurlOptionWithCurrentVersionOfRocket() {
		$api_mock = Mockery::mock( Api::class, [
			'setEmail'   => null,
			'setAuthKey' => null,
		] );
		$api      = $this->getApi( $api_mock );

		$api_mock->shouldReceive( 'setCurlOption' )
		         ->once()
		         ->with( CURLOPT_USERAGENT, 'wp-rocket/3.5' );

		$api->set_api_credentials( null, null, null );
	}

	/**
	 * Test should set the API key on the API.
	 */
	public function testShouldSetZoneId() {
		$api_mock = Mockery::mock( Api::class, [
			'setEmail'      => null,
			'setAuthKey'    => null,
			'setCurlOption' => null,
		] );

		$api     = $this->getApi( $api_mock );
		$zone_id = $this->get_reflective_property( 'zone_id', $api );

		$api->set_api_credentials( null, null, 'zone1' );
		$this->assertSame( 'zone1', $zone_id->getValue( $api ) );

		$api->set_api_credentials( 'test@example.com', '', 'zone10' );
		$this->assertSame( 'zone10', $zone_id->getValue( $api ) );

		$api->set_api_credentials( 'test@example.com', 'API_KEY', 'zone1234' );
		$this->assertSame( 'zone1234', $zone_id->getValue( $api ) );
	}
}
