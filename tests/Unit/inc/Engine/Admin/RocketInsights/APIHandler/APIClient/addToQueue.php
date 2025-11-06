<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\APIHandler\APIClient;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\APIHandler\APIClient;
use WP_Rocket\Tests\Unit\HasLoggerTrait;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\APIHandler\APIClient::add_to_queue
 *
 * @group RocketInsights
 */
class Test_AddToQueue extends TestCase {
	use HasLoggerTrait;

	/**
	 * Options mock.
	 *
	 * @var Mockery\MockInterface|Options_Data
	 */
	protected $options;

	/**
	 * API Client instance.
	 *
	 * @var APIClient
	 */
	protected $api_client;

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
		Functions\expect( 'user_trailingslashit' )->with( $config['url'] )->andReturn( $config['url_trailing'] );
		Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_SAAS_API_URL', false )->andReturn( $config['api_url'] );
		Functions\expect( 'apply_filters' )->with( 'rocket_saas_api_queued_url', $config['url_trailing'], 'rocket_insights' )->andReturn( $config['url_filtered'] );
		
		// Options->get() is called twice: once for email, once for key, plus twice more in handle_request for credentials
		$this->options->shouldReceive( 'get' )->with( 'consumer_email', '' )->andReturn( $config['email'] );
		$this->options->shouldReceive( 'get' )->with( 'consumer_key', '' )->andReturn( $config['key'] );
		
		Functions\expect( 'wp_json_encode' )->andReturn( $config['request_body_json'] );
		Functions\expect( 'wp_remote_request' )->with( $config['request_uri'], $config['args'] )->andReturn( $config['response'] );

		$this->configure_check_response( $config );
		
		$result = $this->api_client->add_to_queue( $config['url'], $config['options'], $config['custom_args'] );
		
		$this->assertEquals( $expected, $result );
	}

	/**
	 * Test that custom timeout in $args is properly merged.
	 */
	public function testShouldMergeCustomTimeoutInArgs() {
		$custom_timeout = 10;
		$url            = 'https://example.com';
		$url_trailing   = 'https://example.com/';
		$response_body  = wp_json_encode( [ 'uuid' => 'test-uuid-123' ] );
		
		Functions\expect( 'user_trailingslashit' )->with( $url )->andReturn( $url_trailing );
		Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_SAAS_API_URL', false )->andReturn( 'https://api.example.com/' );
		Functions\expect( 'apply_filters' )->with( 'rocket_saas_api_queued_url', $url_trailing, 'rocket_insights' )->andReturn( $url_trailing );
		
		$this->options->shouldReceive( 'get' )->with( 'consumer_email', '' )->andReturn( 'test@example.com' );
		$this->options->shouldReceive( 'get' )->with( 'consumer_key', '' )->andReturn( 'test-key-123' );
		
		Functions\expect( 'wp_json_encode' )->andReturn( wp_json_encode( [ 'test' => 'data' ] ) );
		
		$response = [
			'response' => [
				'code'    => 200,
				'message' => 'OK',
			],
			'body'     => $response_body,
		];
		
		// Verify the timeout is passed in the args
		Functions\expect( 'wp_remote_request' )
			->with(
				'https://api.example.com/performance/',
				Mockery::on( function( $args ) use ( $custom_timeout ) {
					$this->assertArrayHasKey( 'timeout', $args );
					$this->assertEquals( $custom_timeout, $args['timeout'] );
					$this->assertArrayHasKey( 'body', $args );
					$this->assertEquals( 'POST', $args['method'] );
					return true;
				} )
			)
			->andReturn( $response );

		Functions\expect( 'wp_remote_retrieve_response_code' )->with( $response )->andReturn( 200 );
		Functions\expect( 'wp_remote_retrieve_body' )->with( $response )->andReturn( $response_body );

		$result = $this->api_client->add_to_queue( $url, [], [ 'timeout' => $custom_timeout ] );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'uuid', $result );
		$this->assertEquals( 'test-uuid-123', $result['uuid'] );
	}

	/**
	 * Test that default args work when $args is empty.
	 */
	public function testShouldUseDefaultArgsWhenCustomArgsEmpty() {
		$url            = 'https://example.com';
		$url_trailing   = 'https://example.com/';
		$response_body  = wp_json_encode( [ 'uuid' => 'test-uuid-456' ] );
		
		Functions\expect( 'user_trailingslashit' )->with( $url )->andReturn( $url_trailing );
		Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_SAAS_API_URL', false )->andReturn( 'https://api.example.com/' );
		Functions\expect( 'apply_filters' )->with( 'rocket_saas_api_queued_url', $url_trailing, 'rocket_insights' )->andReturn( $url_trailing );
		
		$this->options->shouldReceive( 'get' )->with( 'consumer_email', '' )->andReturn( 'test@example.com' );
		$this->options->shouldReceive( 'get' )->with( 'consumer_key', '' )->andReturn( 'test-key-123' );
		
		Functions\expect( 'wp_json_encode' )->andReturn( wp_json_encode( [ 'test' => 'data' ] ) );
		
		$response = [
			'response' => [
				'code'    => 200,
				'message' => 'OK',
			],
			'body'     => $response_body,
		];
		
		// Verify no custom timeout when not provided
		Functions\expect( 'wp_remote_request' )
			->with(
				'https://api.example.com/performance/',
				Mockery::on( function( $args ) {
					$this->assertArrayNotHasKey( 'timeout', $args );
					$this->assertArrayHasKey( 'body', $args );
					$this->assertEquals( 'POST', $args['method'] );
					return true;
				} )
			)
			->andReturn( $response );

		Functions\expect( 'wp_remote_retrieve_response_code' )->with( $response )->andReturn( 200 );
		Functions\expect( 'wp_remote_retrieve_body' )->with( $response )->andReturn( $response_body );

		$result = $this->api_client->add_to_queue( $url, [] );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'uuid', $result );
		$this->assertEquals( 'test-uuid-456', $result['uuid'] );
	}

	/**
	 * Configure check response expectations.
	 *
	 * @param array $config Configuration array.
	 */
	protected function configure_check_response( $config ) {
		Functions\expect( 'wp_remote_retrieve_response_code' )->with( $config['response'] )->andReturn( $config['code'] );
		
		if ( ! $config['is_succeed'] ) {
			Functions\expect( 'wp_remote_retrieve_response_message' )->with( $config['response'] )->andReturn( $config['message'] );
			return;
		}
		
		Functions\expect( 'wp_remote_retrieve_body' )->with( $config['response'] )->andReturn( $config['body'] );
	}
}
