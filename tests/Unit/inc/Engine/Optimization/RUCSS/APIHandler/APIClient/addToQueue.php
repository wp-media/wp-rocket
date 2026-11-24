<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\APIHandler\APIClient;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\RUCSS\APIHandler\APIClient;
use WP_Rocket\Tests\Unit\HasLoggerTrait;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\JobManager\APIHandler\APIClient::add_to_queue
 *
 * @group  SaaS
 */
class Test_AddToQueue extends TestCase {
	use HasLoggerTrait;

	protected $options;
	protected $client;

	protected function setUp(): void
	{
		parent::setUp();
		$this->options = Mockery::mock(Options_Data::class);
		$this->client = new APIClient($this->options);
		$this->set_logger($this->client);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$blocked = isset($config['blocked_offset']);

		$transients = [
			'wp_rocket_rucss_feature_disabled' => $blocked ? time() + $config['blocked_offset'] : false,
			'wp_rocket_rucss_errors_count'     => $config['errors_count'] ?? false,
		];
		Functions\when('get_transient')->alias(fn($key) => $transients[$key] ?? false);

		if ($blocked) {
			Functions\expect('wp_remote_request')->never();
			Functions\expect('set_transient')->never();
			$this->options->shouldReceive('get')->never();
			Functions\expect('add_query_arg')->with([ 'nowprocket' => 1 ], $config['url'])->andReturn($config['url']);
			$this->assertEquals($expected, $this->client->add_to_queue($config['url'], $config['options']));
			return;
		}

		Functions\expect('add_query_arg')->with([ 'nowprocket' => 1 ], $config['url'])->andReturn($config['url']);
		Functions\expect('rocket_get_constant')->with('WP_ROCKET_SAAS_API_URL', false)->andReturn($config['api_url']);
		$this->options->expects()->get('consumer_email', '')->andReturn($config['email']);
		$this->options->expects()->get('consumer_key', '')->andReturn($config['key']);
		Functions\expect('wp_remote_request')->with($config['request_uri'], $config['args'])->andReturn($config['response']);
		Functions\expect('wp_remote_retrieve_body')->with($config['response'])->andReturn($config['body']);

		if (isset($config['feature_disabled'])) {
			$this->configureFeatureDisabled($config);
		} else {
			$this->configureCheckResponse($config);
			$this->configeAuthorized($config);
		}

		$this->assertEquals($expected, $this->client->add_to_queue($config['url'], $config['options']));
	}

	protected function configureFeatureDisabled($config) {
		Functions\expect('wp_remote_retrieve_header')->with($config['response'], 'retry-after')->andReturn($config['retry_after'] ?? '');
		Functions\expect('wp_remote_retrieve_response_code')->never();

		if (! $config['feature_disabled']) {
			Functions\expect('set_transient')->never();
			return;
		}

		$delay = $config['expected_delay'];
		Functions\expect('set_transient')->once()->with(
			'wp_rocket_rucss_feature_disabled',
			Mockery::on(function ($value) use ($delay) {
				return abs($value - (time() + $delay)) <= 2;
			}),
			$delay
		);
	}

	protected function configeAuthorized($config) {
		if(! $config['is_unauthorized']) {
			return;
		}
		Functions\expect('update_option')->with('wp_rocket_no_licence', true );
		Functions\expect('update_rocket_option')->with('remove_unused_css', 0);
	}

	protected function configureCheckResponse($config) {
		Functions\expect('wp_remote_retrieve_header')->never();
		Functions\expect('wp_remote_retrieve_response_code')->with($config['response'])->andReturn($config['code']);
		if(! $config['is_succeed']) {
			Functions\expect('set_transient')->with('wp_rocket_rucss_errors_count', $config['errors_count'] + 1, 5 * MINUTE_IN_SECONDS);
			Functions\expect('wp_remote_retrieve_response_message')->with($config['response'])->andReturn($config['message']);
			return;
		}
		Functions\expect('delete_transient')->with('wp_rocket_rucss_errors_count');
		Functions\expect('set_transient')->never();
		Functions\expect('wp_parse_args')->with($config['to_merge'], $config['default'])->andReturn($config['merged']);
	}
}
