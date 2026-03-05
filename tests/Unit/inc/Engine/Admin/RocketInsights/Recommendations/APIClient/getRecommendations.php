<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\APIClient;

use Brain\Monkey\Functions;
use Mockery;
use WP_Error;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\APIClient;
use WP_Rocket\Tests\Unit\HasLoggerTrait;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\APIClient::get_recommendations
 *
 * @group RocketInsights
 * @group Recommendations
 */
class Test_GetRecommendations extends TestCase {
	use HasLoggerTrait;

	/**
	 * Options mock.
	 *
	 * @var Mockery\MockInterface|Options_Data
	 */
	private $options;

	/**
	 * API Client instance.
	 *
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->options    = Mockery::mock( Options_Data::class );
		$this->api_client = new APIClient( $this->options );
		$this->set_logger( $this->api_client );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->options->shouldReceive( 'get' )->with( 'consumer_email', '' )->andReturn( 'test@example.org' );
		$this->options->shouldReceive( 'get' )->with( 'consumer_key', '' )->andReturn( 'xxxxxx' );

		// Early return case: missing email - no mocks needed
		if ( empty( $config['params']['email'] ) ) {
			$result = $this->api_client->get_recommendations( $config['params'], $config['custom_args'] );

			$this->assertInstanceOf( WP_Error::class, $result );
			$this->assertSame( $expected['error_code'], $result->get_error_code() );
			return;
		}

		// Mock rocket_get_constant for API URL
		Functions\expect( 'rocket_get_constant' )
			->with( 'WP_ROCKET_SAAS_API_URL', false )
			->andReturn( $config['api_url'] );

		// Mock wp_remote_request (used by handle_get)
		Functions\expect( 'wp_remote_request' )
			->with( $config['request_uri'], $config['request_args'] )
			->andReturn( $config['response'] );

		// Mock is_wp_error
		Functions\expect( 'is_wp_error' )
			->with( $config['response'] )
			->andReturn( $config['is_wp_error'] );

		// Mock WordPress response helper functions only if NOT WP_Error
		if ( ! $config['is_wp_error'] ) {
			Functions\expect( 'wp_remote_retrieve_response_code' )
				->with( $config['response'] )
				->andReturn( $config['response']['response']['code'] ?? 0 );

			Functions\expect( 'wp_remote_retrieve_response_message' )
				->with( $config['response'] )
				->andReturn( $config['response']['response']['message'] ?? '' );

			Functions\expect( 'wp_remote_retrieve_body' )
				->with( $config['response'] )
				->andReturn( $config['response']['body'] ?? '' );
		}

		$result = $this->api_client->get_recommendations( $config['params'], $config['custom_args'] );

		// Assertions
		if ( $expected['is_error'] ) {
			$this->assertInstanceOf( WP_Error::class, $result );
			$this->assertSame( $expected['error_code'], $result->get_error_code() );
		} else {
			$this->assertIsArray( $result );
			$this->assertArrayHasKey( 'code', $result );
			$this->assertSame( $expected['code'], $result['code'] );
			$this->assertArrayHasKey( 'data', $result );
			$this->assertArrayHasKey( 'recommendations', $result['data'] );
			$this->assertArrayHasKey( 'metadata', $result['data'] );
		}
	}
}
