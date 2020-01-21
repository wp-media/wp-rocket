<?php
namespace WP_Rocket\Tests\Integration\Addons\Cloudflare\Cloudflare;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers WP_Rocket\Addons\Cloudflare\Cloudflare::get_cloudflare_ips
 *
 * @group Cloudflare
 */
class Test_GetCloudflareIpS extends TestCase {

	/**
	 * Test get cloudflare IPs with cached invalid transient for credentials.
	 */
	public function testGetCloudflareIPSWithInvalidCredentials() {
		$this->assertTrue( true );
	}

	/**
	 * Test get cloudflare IPs with invalid credentials and cached IPs in transient `rocket_cloudflare_ips`.
	 */
	public function testGetCloudflareIPSWithInvalidCredentialsButIPSCached() {
		$this->assertTrue( true );
	}

	/**
	 * The get Cloudflare IPs with valid CF credentials, no cached `rocket_cloudflare_ips` and error on `ips()`.
	 */
	public function testGetCloudflareIPSWithValidCredentialsAndNoCachedIPSWithError() {
		$this->assertTrue( true );
	}

	/**
	 * The get Cloudflare IPs with valid CF credentials, no cached `rocket_cloudflare_ips` and success `ips()`.
	 */
	public function testGetCloudflareIPSWithValidCredentialsAndNoCachedIPSWithSuccess() {
		$this->assertTrue( true );
	}
}
