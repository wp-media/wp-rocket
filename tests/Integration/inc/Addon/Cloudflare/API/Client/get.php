<?php

namespace WP_Rocket\Tests\Integration\Inc\Addon\Cloudflare\API\Client;

use WP_Error;
use WP_Rocket\Addon\Cloudflare\API\Client;
use WP_Rocket\Addon\Cloudflare\Auth\APIKey;
use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering WP_Rocket\Addon\Cloudflare\API\Client::get
 *
 * @group Cloudflare
 */
class TestGet extends TestCase {
	use HttpRequestTrait;

	// Not needed here: the settings trait's set_up() write to wp_rocket_settings triggers the
	// Cloudflare Subscriber's own real zone lookup before this test gets a chance to mock it.
	protected static $use_settings_trait = false;

	protected $rocket_version = '3.13';

	public function set_up() {
		parent::set_up();

		$this->setup_http();
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
			Client::CLOUDFLARE_API . $config['path'] => $config['response'],
		];

		$auth = new APIKey( $config['email'], $config['api_key'] );
		$client = new Client( $auth );
		$result = $client->get( $config['path'], $config['data'] );

		if ( 'error' === $expected['result'] ) {
			$this->assertInstanceOf( WP_Error::class, $result );

				$this->assertSame(
					$expected['error_code'],
					$result->get_error_code()
				);
		} else {
			$this->assertSame(
				$expected['result'],
				$result
			);
		}
	}
}
