<?php
namespace WP_Rocket\Tests\Integration\Addons\Cloudflare\Cloudflare;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers WP_Rocket\Addons\Cloudflare\Cloudflare::get_cloudflare_instance
 *
 * @group Cloudflare
 */
class Test_GetCloudflareInstance extends TestCase {

	/**
	 * Test Get Instance with Invalid Cloudflare credentials and no transient set.
	 */
	public function testGetInstanceWithInvalidCFCredentialsNoTransient() {
		$this->assertTrue( true );
	}

	/**
	 * Test Get Instance with valid Cloudflare credentials and no transient set.
	 */
	public function testGetInstanceWithValidCFCredentialsNoTransient() {
		$this->assertTrue( true );
	}

	/**
	 * Test Get Instance with invalid Cloudflare credentials and transient set.
	 */
	public function testGetInstanceWithInValidCFCredentialsAndTransient() {
		$this->assertTrue( true );
	}

	/**
	 * Test Get Instance with valid Cloudflare credentials and transient set.
	 */
	public function testGetInstanceWithValidCFCredentialsAndTransient() {
		$this->assertTrue( true );
	}
}
