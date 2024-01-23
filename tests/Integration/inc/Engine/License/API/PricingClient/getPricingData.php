<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\API\PricingClient;

use WP_Rocket\Engine\License\API\PricingClient;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\API\PricingClient::get_pricing_data
 *
 * @group  License
 */
class GetPricingData extends TestCase {
	protected static $transients = [
		'wp_rocket_pricing',
		'wp_rocket_pricing_timeout',
		'wp_rocket_pricing_timeout_active'
	];

	private $response;

	public function set_up() {
		parent::set_up();

		delete_transient( 'wp_rocket_pricing' );
		delete_transient( 'wp_rocket_pricing_timeout' );
		delete_transient( 'wp_rocket_pricing_timeout_active' );
	}

	public function tear_down() {
		delete_transient( 'wp_rocket_pricing' );
		delete_transient( 'wp_rocket_pricing_timeout' );
		delete_transient( 'wp_rocket_pricing_timeout_active' );

		remove_filter( 'pre_http_request', [ $this, 'set_response' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$client = new PricingClient();

		$this->response = $config['response'];

		add_filter( 'pre_http_request', [ $this, 'set_response' ] );

		if ( true === $config['pricing-transient'] ) {
			set_transient( 'wp_rocket_pricing', $expected['result'] );
		}

		if ( false !== $config['timeout-duration'] ) {
			set_transient( 'wp_rocket_pricing_timeout', $config['timeout-duration'], WEEK_IN_SECONDS );
		}

		if ( true === $config['timeout-active'] ) {
			set_transient( 'wp_rocket_pricing_timeout_active', true, WEEK_IN_SECONDS );
		}

		$this->assertEquals(
			$expected['result'],
			$client->get_pricing_data()
		);

		if ( false !== $config['timeout-duration'] ) {
			$this->assertEquals(
				$expected['timeout-duration'],
				get_transient( 'wp_rocket_pricing_timeout' )
			);
		}
	}

	public function set_response() {
		return $this->response;
	}
}
