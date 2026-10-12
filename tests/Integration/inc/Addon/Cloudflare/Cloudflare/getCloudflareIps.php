<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\Cloudflare;

use WP_Rocket\Addon\Cloudflare\API\Client;
use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering WP_Rocket\Addon\Cloudflare\Cloudflare::get_cloudflare_ips
 *
 * @group Cloudflare
 */
class TestGetCloudflareIps extends TestCase {
	use HttpRequestTrait;

	// Not needed here: the settings trait's set_up() write to wp_rocket_settings triggers the
	// Cloudflare Subscriber's own real zone lookup before this test gets a chance to mock it.
	protected static $use_settings_trait = false;

	private $cloudflare;

	public function set_up() {
		parent::set_up();

		$this->setup_http();

		$container = apply_filters( 'rocket_container', null );

		$this->cloudflare = $container->get( 'cloudflare' );
	}

	public function tear_down() {
		$this->tear_down_http();

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->config['http'] = [
			Client::CLOUDFLARE_API . '/ips' => $config['response'],
		];

		if ( $config['transient'] ) {
			set_transient( 'rocket_cloudflare_ips', $config['transient'] );
		}

		$result = $this->cloudflare->get_cloudflare_ips();

		$this->assertNotFalse( get_transient( 'rocket_cloudflare_ips' ) );
		$this->assertEquals(
			$expected,
			$result
		);
	}
}
