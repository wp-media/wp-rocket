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
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\APIHandler\APIClient::get_queue_job_status
 *
 * @group RocketInsights
 */
class Test_GetQueueJobStatus extends TestCase {
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
		$until = isset( $config['blocked_offset'] ) ? time() + $config['blocked_offset'] : false;

		Functions\when( 'get_transient' )->alias( fn( $key ) => 'wp_rocket_rocket_insights_feature_disabled' === $key ? $until : false );
		Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_SAAS_API_URL', false )->andReturn( $config['api_url'] );
		Functions\expect( 'set_transient' )->never();

		$this->options->shouldReceive( 'get' )->with( 'consumer_email', '' )->once()->andReturn( $config['email'] );
		$this->options->shouldReceive( 'get' )->with( 'consumer_key', '' )->once()->andReturn( $config['key'] );

		Functions\expect( 'wp_remote_request' )->with( $config['request_uri'], $config['args'] )->once()->andReturn( $config['response'] );
		Functions\expect( 'wp_remote_retrieve_response_code' )->with( $config['response'] )->andReturn( $config['code'] );
		Functions\expect( 'wp_remote_retrieve_body' )->with( $config['response'] )->andReturn( $config['body'] );

		$this->assertEquals( $expected, $this->api_client->get_queue_job_status( $config['test_id'], $config['queue_name'] ) );
	}
}
