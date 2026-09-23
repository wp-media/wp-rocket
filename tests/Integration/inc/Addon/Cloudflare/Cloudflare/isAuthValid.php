<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\Cloudflare;

use WP_Error;
use WP_Rocket\Addon\Cloudflare\API\Client;
use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering WP_Rocket\Addon\Cloudflare\Cloudflare::is_auth_valid
 *
 * @group Cloudflare
 */
class TestIsAuthValid extends TestCase {
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
			Client::CLOUDFLARE_API . "zones/{$config['zone_id']}" => $config['response'],
		];

		$result = $this->cloudflare->is_auth_valid( $config['zone_id'] );

		if ( 'error' === $expected ) {
			$this->assertInstanceOf(
				WP_Error::class,
				$result
			);
		} else {
			$this->assertTrue( $result );
		}
	}
}
