<?php

namespace WP_Rocket\Tests\Integration\inc\Addons\Cloudflare;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * This test validates that Cloudflare addon is not loaded into the container or memory when "do_cloudflare" is off.
 * @group  Cloudflare
 * @group  Addons
 */
class Test_Addon extends TestCase {

	public function testDoCloudflareIsOff() {
		$this->assertFalse( get_rocket_option( 'do_cloudflare', false ) );
	}

	public function testContainerDoesNotHaveCloudflare() {
		$container = apply_filters( 'rocket_container', '' );

		$this->assertFalse( $container->has( 'cloudflare_subscriber' ) );
		$this->assertFalse( $container->has( 'cloudflare' ) );
		$this->assertFalse( $container->has( 'cloudflare_api' ) );
	}
}
