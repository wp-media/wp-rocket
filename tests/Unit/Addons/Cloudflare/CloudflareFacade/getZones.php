<?php

namespace WP_Rocket\Tests\Unit\Addons\Cloudflare\CloudflareFacade;

use Cloudflare\Api;
use Mockery;
use stdClass;
use WP_Rocket\Addons\Cloudflare\CloudflareFacade;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers WP_Rocket\Addons\Cloudflare\CloudflareFacade::get_zones
 *
 * @group Cloudflare
 */
class Test_GetZones extends TestCase {

	public function testShouldGetZonesWhenZoneIdIsSet() {
		$api_mock = Mockery::mock( Api::class );
		$api = new CloudflareFacade( $api_mock );

		$api_mock->shouldReceive( 'get' )->once()->with( 'zones/' );
		$this->assertInstanceOf( stdClass::class, $api->get_zones() );
	}
}
