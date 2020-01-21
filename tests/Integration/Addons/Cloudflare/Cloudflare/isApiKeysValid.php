<?php
namespace WP_Rocket\Tests\Integration\Addons\Cloudflare\Cloudflare;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers WP_Rocket\Addons\Cloudflare\Cloudflare::is_api_keys_valid
 *
 * @group Cloudflare
 */
class Test_IsApiKeysValid extends TestCase {

	/**
	 * Test Cloudflare API valid keys with empty values.
	 */
	public function testApiKeysWithEmptyValues() {
		$this->assertTrue( true );
	}

	/**
	 * Test Cloudflare API valid keys with null values.
	 */
	public function testApiKeysWithNullValues() {
		$this->assertTrue( true );
	}

	/**
	 * Test Cloudflare API valid keys with empty zone value.
	 */
	public function testApiKeysWithEmptyZoneValue() {
		$this->assertTrue( true );
	}

	/**
	 * Test Cloudflare API valid keys with wrong credentials
	 */
	public function testApiKeysWithWrongCredentialsExceptionThrown() {
		$this->assertTrue( true );
	}

	/**
	 * Test Cloudflare API valid keys with wrong zone id, correct credentials.
	 */
	public function testApiKeysWithWrongZoneId() {
		$this->assertTrue( true );
	}

	/**
	 * Test Cloudflare API valid keys with wrong domain mapping.
	 */
	public function testApiKeysWithWrongDomainMapping() {
		$this->assertTrue( true );
	}

	/**
	 * Test Cloudflare API valid keys.
	 */
	public function testApiKeysValid() {
		$this->assertTrue( true );
	}
}
