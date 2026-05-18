<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\RemoteSettingsClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\RemoteSettingsClient::get_remote_settings_data
 *
 * @group License
 */
class RemoteSettingsClientGetRemoteSettingsData extends TestCase {
	private $client;
	private $options;

	public function setUp(): void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->client  = new RemoteSettingsClient( $this->options );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		Functions\when( 'wp_remote_retrieve_body' )
			->justReturn(
				is_array( $config['response'] )
					? $config['response']['body']
					: ''
			);

		Functions\expect( 'get_transient' )
			->atLeast()->once()
			->with( 'wp_rocket_remote_settings' )
			->andReturn( false !== $config['transient'] ? $config['transient'] : false );

		if ( false === $config['transient'] ) {
			// Customer data and URL params are built before the HTTP call, so always mock them.
			$this->options->shouldReceive( 'get' )
				->atLeast()->once()
				->with( 'consumer_key', '' )
				->andReturn( 'test_consumer_key' );

			$this->options->shouldReceive( 'get' )
				->atLeast()->once()
				->with( 'consumer_email', '' )
				->andReturn( 'test@example.com' );

			Functions\when( 'sanitize_key' )->returnArg();
			Functions\when( 'home_url' )->justReturn( 'http://example.com' );
			$this->stubWpParseUrl();

			Functions\expect( 'get_transient' )
				->once()
				->with( 'wp_rocket_remote_settings_timeout_active' )
				->andReturn( $config['timeout-active'] );

			if ( ! $config['timeout-active'] && false !== $config['response'] ) {
				Functions\expect( 'get_transient' )
					->once()
					->with( 'wp_rocket_remote_settings_timeout' )
					->andReturn( (int) $config['timeout-duration'] );

				Functions\expect( 'wp_safe_remote_post' )
					->once()
					->andReturn( $config['response'] );

				$is_http_success = is_array( $config['response'] )
					&& ! empty( $config['response']['body'] )
					&& in_array( $config['response']['response']['code'], [ 200, 202 ], true );

				if ( ! $is_http_success ) {
					$duration = $config['timeout-duration']
						? min( [
							2 * $config['timeout-duration'],
							rocket_get_constant( 'DAY_IN_SECONDS' ),
						] )
						: 300;

					Functions\expect( 'set_transient' )
						->with(
							'wp_rocket_remote_settings_timeout',
							$duration,
							rocket_get_constant( 'WEEK_IN_SECONDS' )
						)
						->andAlsoExpectIt()
						->with( 'wp_rocket_remote_settings_timeout_active', true, $duration );
				} else {
					Functions\expect( 'delete_transient' )
						->with( 'wp_rocket_remote_settings_timeout_active' )
						->andAlsoExpectIt()
						->with( 'wp_rocket_remote_settings_timeout' );

					$body_data = json_decode( $config['response']['body'] );

					if ( ! empty( $body_data->success ) && ! empty( $body_data->data ) ) {
						Functions\expect( 'set_transient' )
							->once()
							->with(
								'wp_rocket_remote_settings',
								Mockery::type( 'object' ),
								DAY_IN_SECONDS
							);
					}
				}
			}
		}

		$this->assertEquals( $expected, $this->client->get_remote_settings_data() );
	}
}
