<?php

namespace WP_Rocket\Tests\Integration\Addons\Cloudflare\CloudflareFacade;

use Cloudflare\Api;
use Cloudflare\Exception\AuthenticationException;
use WP_Rocket\Addons\Cloudflare\CloudflareFacade;
use WP_Rocket\Tests\Integration\Addons\Cloudflare\CloudflareTestCase;

/**
 * @covers WP_Rocket\Addons\Cloudflare\CloudflareFacade::get_zones
 *
 * @group  Cloudflare
 */
class Test_GetZones extends CloudflareTestCase {

	public function testShouldThrowErrorWhenNoCredentials() {
		$api = new CloudflareFacade( new Api() );
		$api->set_api_credentials( null, null, null );
		$this->expectException( AuthenticationException::class );
		$this->expectExceptionMessage( 'Authentication information must be provided' );
		$api->get_zones();
	}

	public function testShouldFailWhenInvalid() {
		$api = new CloudflareFacade( new Api() );
		$api->set_api_credentials( self::$email, self::$api_key, 'ZONE_ID' );

		$response = $api->get_zones();
		$this->assertFalse( $response->success );
		$this->assertCount( 2, $response->errors );
		$zone_error = $response->errors[0];
		$this->assertSame( 7003, $zone_error->code );
		$this->assertSame( 'Could not route to /zones/ZONE_ID, perhaps your object identifier is invalid?', $zone_error->message );
	}

	public function testShouldSucceedWhenZoneExists() {
		$api = new CloudflareFacade( new Api() );
		$api->set_api_credentials( self::$email, self::$api_key, self::$zone_id );

		$response = $api->get_zones();
		$this->assertTrue( $response->success );
		$this->assertSame( self::$zone_id, $response->result->id );
	}
}
