<?php

namespace WP_Rocket\Tests\Integration\Addons\Cloudflare\CloudflareFacade;

use Cloudflare\Api;
use WP_Rocket\Addons\Cloudflare\CloudflareFacade;
use WP_Rocket\Tests\Integration\Addons\Cloudflare\CloudflareTestCase;

/**
 * @covers WP_Rocket\Addons\Cloudflare\CloudflareFacade::ips
 *
 * @group  Cloudflare
 */
class Test_Ips extends CloudflareTestCase {

	public function testShouldReturnIps() {
		$api = new CloudflareFacade( new Api() );
		$api->set_api_credentials( self::$email, self::$api_key, null );

		$response = $api->ips();
		$this->assertTrue( $response->success );
		$this->assertContains( '173.245.48.0/20', $response->result->ipv4_cidrs );
		$this->assertContains( '2400:cb00::/32', $response->result->ipv6_cidrs );
	}
}
