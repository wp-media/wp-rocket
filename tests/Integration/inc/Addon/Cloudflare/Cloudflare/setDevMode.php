<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\Cloudflare;

use WP_Error;
use WP_Rocket\Addon\Cloudflare\API\Client;
use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering WP_Rocket\Addon\Cloudflare\Cloudflare::set_dev_mode
 *
 * @group Cloudflare
 */
class TestSetDevMode extends TestCase {
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
		$container = apply_filters( 'rocket_container', null );
		$zone_id   = $container->get( 'options' )->get( 'cloudflare_zone_id', '' );

		$this->config['http'] = [
			Client::CLOUDFLARE_API . "zones/{$zone_id}/settings/development_mode" => $config['response'],
		];

		$result = $this->cloudflare->set_devmode( $config['value'] );

		if ( 'error' !== $expected ) {
			if ( 1 === $config['value'] ) {
				$this->assertNotFalse( wp_next_scheduled( 'rocket_cron_deactivate_cloudflare_devmode' ) );
			} else {
				$this->assertFalse( wp_next_scheduled( 'rocket_cron_deactivate_cloudflare_devmode' ) );
			}
		}

		if ( 'error' === $expected ) {
			$this->assertInstanceOf(
				WP_Error::class,
				$result
			);
		} else {
			$this->assertSame(
				$expected,
				$result
			);
		}
	}
}
