<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\PerformanceMonitoring\APIHandler\APIClient;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\APIHandler\APIClient;
use WP_Rocket\Tests\Unit\HasLoggerTrait;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\PerformanceMonitoring\APIHandler\APIClient::add_to_queue
 *
 * @group PerformanceMonitoring
 */
class Test_AddToQueue extends TestCase {
	use HasLoggerTrait;

	protected $options;
	protected $client;

	protected function setUp(): void {
		parent::setUp();
		$this->options = Mockery::mock( Options_Data::class );
		$this->client  = new APIClient( $this->options );
		$this->set_logger( $this->client );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		Functions\expect( 'user_trailingslashit' )->with( $config['url'] )->andReturn( $config['url_with_slash'] );
		Functions\expect( 'wpm_apply_filters_typed' )->with( 'string', 'rocket_saas_api_queued_url', $config['url_with_slash'], 'performance_monitoring' )->andReturn( $config['url_with_slash'] );
		Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_SAAS_API_URL', false )->andReturn( $config['api_url'] );
		$this->options->expects()->get( 'consumer_email', '' )->andReturn( $config['email'] );
		$this->options->expects()->get( 'consumer_key', '' )->andReturn( $config['key'] );
		$this->options->expects()->get( 'consumer_email', '' )->andReturn( $config['email'] );
		$this->options->expects()->get( 'consumer_key', '' )->andReturn( $config['key'] );
		Functions\expect( 'wp_json_encode' )->with( $config['request_body'] )->andReturn( json_encode( $config['request_body'] ) );
		Functions\expect( 'wp_remote_request' )->with( $config['request_uri'], $config['args'] )->andReturn( $config['response'] );

		$this->configureCheckResponse( $config );
		$this->assertEquals( $expected, $this->client->add_to_queue( $config['url'], $config['options'] ) );
	}

	protected function configureCheckResponse( $config ) {
		Functions\expect( 'wp_remote_retrieve_response_code' )->with( $config['response'] )->andReturn( $config['code'] );
		if ( ! $config['is_succeed'] ) {
			Functions\expect( 'get_transient' )->with( 'wp_rocket_rucss_errors_count' )->andReturn( $config['errors_count'] );
			Functions\expect( 'set_transient' )->with( 'wp_rocket_rucss_errors_count', $config['errors_count'] + 1, 5 * MINUTE_IN_SECONDS );
			Functions\expect( 'wp_remote_retrieve_response_message' )->with( $config['response'] )->andReturn( $config['message'] );
			return;
		}
		Functions\expect( 'delete_transient' )->with( 'wp_rocket_rucss_errors_count' );
		Functions\expect( 'wp_remote_retrieve_body' )->with( $config['response'] )->andReturn( $config['body'] );
	}
}
