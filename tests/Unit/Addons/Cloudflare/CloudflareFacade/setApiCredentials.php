<?php

namespace WP_Rocket\Tests\Unit\Addons\Cloudflare\CloudflareFacade;

use Brain\Monkey\Functions;
use Cloudflare\Api;
use Cloudflare\IPs;
use Cloudflare\Zone\Cache;
use Cloudflare\Zone\Pagerules;
use Cloudflare\Zone\Settings;
use Mockery;
use WP_Rocket\Addons\Cloudflare\CloudflareFacade;
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

	public function testShouldSetEmail() {
		$api_mock = Mockery::mock( Api::class, [
			'setAuthKey'    => null,
			'setCurlOption' => null,
		] );
		$api      = new CloudflareFacade( $api_mock );

		$api_mock->shouldReceive( 'setEmail' )->with( 'test@wp-media.me' );
		$api->set_api_credentials( 'test@wp-media.me', null, null );
	}

	public function testShouldSetApiKey() {
		$api_mock = Mockery::mock( Api::class, [
			'setEmail'      => null,
			'setCurlOption' => null,
		] );
		$api      = new CloudflareFacade( $api_mock );

		$api_mock->shouldReceive( 'setAuthKey' )->with( 'API_KEY' );
		$api->set_api_credentials( 'test@wp-media.me', 'API_KEY', null );
	}

	public function testShouldSetCurlOption() {
		$api_mock = Mockery::mock( Api::class, [
			'setEmail'   => null,
			'setAuthKey' => null,
		] );
		$api      = new CloudflareFacade( $api_mock );

		$api_mock->shouldReceive( 'setCurlOption' )
		         ->once()
		         ->with( CURLOPT_USERAGENT, 'wp-rocket/3.5' );

		$api->set_api_credentials( null, null, null );
	}

	public function testShouldSetZoneId() {
		$api_mock = Mockery::mock( Api::class, [
			'setEmail'   => null,
			'setAuthKey'   => null,
			'setCurlOption' => null,
		] );

		$api      = new CloudflareFacade( $api_mock );
		$zone_id = $this->get_reflective_property( 'zone_id', $api );

		$api->set_api_credentials( null, null, 'zone1' );
		$this->assertSame( 'zone1', $zone_id->getValue( $api ) );

		$api->set_api_credentials( 'test@wp-media.me', '', 'zone10' );
		$this->assertSame( 'zone10', $zone_id->getValue( $api ) );

		$api->set_api_credentials( 'test@wp-media.me', 'API_KEY', 'zone1234' );
		$this->assertSame( 'zone1234', $zone_id->getValue( $api ) );
	}

	public function testShouldSetPageRules() {
		$api_mock = Mockery::mock( Api::class, [
			'setEmail'   => null,
			'setAuthKey'   => null,
			'setCurlOption' => null,
		] );

		$api      = new CloudflareFacade( $api_mock );
		$api->set_api_credentials( 'test@wp-media.me', 'API_KEY', 'zone1234' );
		$page_rules = $this->get_reflective_property( 'page_rules', $api );
		$this->assertInstanceOf( Pagerules::class, $page_rules->getValue( $api ));
	}

	public function testShouldSetCache() {
		$api_mock = Mockery::mock( Api::class, [
			'setEmail'   => null,
			'setAuthKey'   => null,
			'setCurlOption' => null,
		] );

		$api      = new CloudflareFacade( $api_mock );
		$api->set_api_credentials( 'test@wp-media.me', 'API_KEY', 'zone1234' );
		$cache = $this->get_reflective_property( 'cache', $api );
		$this->assertInstanceOf( Cache::class, $cache->getValue( $api ));
	}

	public function testShouldSetSettings() {
		$api_mock = Mockery::mock( Api::class, [
			'setEmail'   => null,
			'setAuthKey'   => null,
			'setCurlOption' => null,
		] );

		$api      = new CloudflareFacade( $api_mock );
		$api->set_api_credentials( 'test@wp-media.me', 'API_KEY', 'zone1234' );
		$settings = $this->get_reflective_property( 'settings', $api );
		$this->assertInstanceOf( Settings::class, $settings->getValue( $api ));
	}

	public function testShouldSetIps() {
		$api_mock = Mockery::mock( Api::class, [
			'setEmail'   => null,
			'setAuthKey'   => null,
			'setCurlOption' => null,
		] );

		$api      = new CloudflareFacade( $api_mock );
		$api->set_api_credentials( 'test@wp-media.me', 'API_KEY', 'zone1234' );
		$ips = $this->get_reflective_property( 'ips', $api );
		$this->assertInstanceOf( IPs::class, $ips->getValue( $api ));
	}
}
