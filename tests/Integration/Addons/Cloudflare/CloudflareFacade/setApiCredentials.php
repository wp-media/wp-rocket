<?php

namespace WP_Rocket\Tests\Integration\Addons\Cloudflare\CloudflareFacade;

use Cloudflare\Api;
use Cloudflare\Exception\AuthenticationException;
use Cloudflare\IPs;
use Cloudflare\Zone\Cache;
use Cloudflare\Zone\Pagerules;
use Cloudflare\Zone\Settings;
use WP_Rocket\Addons\Cloudflare\CloudflareFacade;
use WP_Rocket\Tests\Integration\Addons\Cloudflare\CloudflareTestCase;

/**
 * @covers WP_Rocket\Addons\Cloudflare\CloudflareFacade::set_api_credentials
 *
 * @group  Cloudflare
 */
class Test_SetApiCredentials extends CloudflareTestCase {

	public function testShouldSetEmail() {
		$api = new Api();
		$cf  = new CloudflareFacade( $api );

		$cf->set_api_credentials( null, null, null );
		$this->assertNull( $api->email );

		$cf->set_api_credentials( 'test@example.com', null, null );
		$this->assertSame( 'test@example.com', $api->email );
	}

	public function testShouldSetApiKey() {
		$api = new Api();
		$cf  = new CloudflareFacade( $api );

		$cf->set_api_credentials( null, null, null );
		$this->assertNull( $api->auth_key );

		$cf->set_api_credentials( null, 'someAuthKey', null );
		$this->assertSame( 'someAuthKey', $api->auth_key );
	}

	public function testShouldSetCurlOption() {
		$api = new Api();
		$cf  = new CloudflareFacade( $api );

		$this->assertNull( $api->curl_options );
		$cf->set_api_credentials( 'test@example.com', 'someAuthKey', 'zone1' );
		$this->assertArrayHasKey( CURLOPT_USERAGENT, $api->curl_options );
		$this->assertSame( 'wp-rocket/' . WP_ROCKET_VERSION, $api->curl_options[ CURLOPT_USERAGENT ] );
	}

	public function testShouldSetPageRules() {
		$cf = new CloudflareFacade( new Api() );
		$cf->set_api_credentials( 'test@example.com', 'someAuthKey', 'zone1' );
		$page_rules = $this->get_reflective_property( 'page_rules', $cf );
		$this->assertInstanceOf( Pagerules::class, $page_rules->getValue( $cf ) );
	}

	public function testShouldSetCache() {
		$cf = new CloudflareFacade( new Api() );
		$cf->set_api_credentials( 'test@example.com', 'someAuthKey', 'zone1' );
		$cache = $this->get_reflective_property( 'cache', $cf );
		$this->assertInstanceOf( Cache::class, $cache->getValue( $cf ) );
	}

	public function testShouldSetSettings() {
		$cf = new CloudflareFacade( new Api() );
		$cf->set_api_credentials( 'test@example.com', 'someAuthKey', 'zone1' );
		$settings = $this->get_reflective_property( 'settings', $cf );
		$this->assertInstanceOf( Settings::class, $settings->getValue( $cf ) );
	}

	public function testShouldSetIps() {
		$cf = new CloudflareFacade( new Api() );
		$cf->set_api_credentials( 'test@example.com', 'someAuthKey', 'zone1' );
		$ips = $this->get_reflective_property( 'ips', $cf );
		$this->assertInstanceOf( IPs::class, $ips->getValue( $cf ) );
	}

	public function testShouldThrowErrorWhenInvalidCredentials() {
		$api = new Api();
		$cf  = new CloudflareFacade( $api );
		$cf->set_api_credentials( null, null, null );

		$this->expectException( AuthenticationException::class );
		$this->expectExceptionMessage( 'Authentication information must be provided' );
		$api->get( 'test' );
	}

	public function testShouldGetResponseWhenCredentialsAreValid() {
		$api = new Api();
		$cf  = new CloudflareFacade( $api );
		$cf->set_api_credentials( self::$email, self::$api_key, null );

		$response = $api->get( 'test' );
		$this->assertSame( 'get', $response->method );
	}
}
