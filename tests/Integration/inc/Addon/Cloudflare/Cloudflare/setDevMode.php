<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\Cloudflare;

use WP_Error;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers WP_Rocket\Addon\Cloudflare\Cloudflare::set_dev_mode
 *
 * @group Cloudflare
 */
class TestSetDevMode extends TestCase {
	private $cloudflare;
	private $response;

	public function set_up() {
		parent::set_up();

		$container = apply_filters( 'rocket_container', null );

		$this->cloudflare = $container->get( 'cloudflare' );
	}

	public function tear_down() {
		remove_filter( 'pre_http_request', [ $this, 'http_request'] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->response = $config['response'];

		add_filter( 'pre_http_request', [ $this, 'http_request'] );

		$result = $this->cloudflare->set_devmode( $config['value'] );

		if ( 1 === $config['value'] ) {
			$this->assertNotFalse( wp_get_scheduled_event( 'rocket_cron_deactivate_cloudflare_devmode' ) );
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

	public function http_request() {
		return $this->response;
	}
}
