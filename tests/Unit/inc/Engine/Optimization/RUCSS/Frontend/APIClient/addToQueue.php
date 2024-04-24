<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Frontend\APIClient;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient::add_to_queue
 *
 * @group  RUCSS
 */
class Test_AddToQueue extends TestCase {

	protected $options;
	protected $client;

	protected function setUp(): void
	{
		parent::setUp();
		$this->options = Mockery::mock(Options_Data::class);
		$this->client = new APIClient($this->options);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		Functions\expect('add_query_arg')->with([ 'nowprocket' => 1 ], $config['url'])->andReturn($config['url']);
		Functions\expect('rocket_get_constant')->with('WP_ROCKET_SAAS_API_URL', false)->andReturn($config['api_url']);
		$this->options->expects()->get('consumer_email', '')->andReturn($config['email']);
		$this->options->expects()->get('consumer_key', '')->andReturn($config['key']);
		Functions\expect('wp_remote_request')->with($config['request_uri'], $config['args'])->andReturn($config['response']);

		$this->configureCheckResponse($config);
		$this->configeAuthorized($config);
		$this->assertEquals($expected, $this->client->add_to_queue($config['url'], $config['options']));
	}

	protected function configeAuthorized($config) {
		if(! $config['is_unauthorized']) {
			return;
		}
		Functions\expect('update_option')->with('wp_rocket_no_licence', true );
		Functions\expect('update_rocket_option')->with('remove_unused_css', 0);
	}

	protected function configureCheckResponse($config) {
		Functions\expect('wp_remote_retrieve_response_code')->with($config['response'])->andReturn($config['code']);
		if(! $config['is_succeed']) {
			Functions\expect('get_transient')->with('wp_rocket_rucss_errors_count')->andReturn($config['errors_count']);
			Functions\expect('set_transient')->with('wp_rocket_rucss_errors_count', $config['errors_count'] + 1, 5 * MINUTE_IN_SECONDS);
			Functions\expect('wp_remote_retrieve_response_message')->with($config['response'])->andReturn($config['message']);
			return;
		}
		Functions\expect('delete_transient')->with('wp_rocket_rucss_errors_count');
		Functions\expect('wp_remote_retrieve_body')->with($config['response'])->andReturn($config['body']);
		Functions\expect('wp_parse_args')->with($config['to_merge'], $config['default'])->andReturn($config['merged']);
	}
}
