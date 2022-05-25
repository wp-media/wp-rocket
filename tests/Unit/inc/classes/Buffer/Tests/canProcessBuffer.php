<?php

namespace WP_Rocket\Tests\Unit\inc\classes\Buffer\Tests;

use WP_Rocket\Buffer\Config;
use WP_Rocket\Buffer\Tests;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use Mockery;

/**
 * @covers \WP_Rocket\Buffer\Tests::can_process_buffer
 *
 * @group  Buffer
 */
class Test_CanProcessBuffer extends TestCase {

	protected $tests;
	protected $config;

	public function setUp(): void
	{
		parent::setUp();
		$this->config = \Mockery::mock(Config::class);
		$this->tests = Mockery::mock(Tests::class.'[has_donotcachepage,is_404,is_search,is_feed_uri,is_html,get_http_response_code]',
			[$this->config]);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->configureRocketFunction($config);
		$this->configureHttpResponse($config);
		$this->configureCache($config);
		$this->configure404($config);
		$this->configureSearch($config);
		$this->configureHTML($config);
		$this->assertSame($expected['buffer_results'], $this->tests->can_process_buffer($config['buffer']));
		$this->assertSame($expected['error'], $this->tests->get_last_error());
	}

	protected function configureRocketFunction($config) {
		if(! key_exists('rocket_exist', $config)) {
			return;
		}
		Functions\when( 'rocket_mkdir_p' )->justReturn( '' );
	}

	protected function configureHttpResponse($config) {
		if(! key_exists('response_code', $config)) {
			return;
		}
		$this->tests->expects()->get_http_response_code()->andReturn($config['response_code']);
	}

	protected function configureCache($config) {
		if(! key_exists('cache', $config)) {
			return;
		}
		$this->tests->expects()->has_donotcachepage()->andReturn($config['cache']);
	}

	protected function configure404($config) {
		if(! key_exists('404', $config)) {
			return;
		}
		$this->tests->expects()->is_404()->andReturn($config['404']);
	}

	protected function configureSearch($config) {
		if(! key_exists('search', $config)) {
			return;
		}
		$this->tests->expects()->is_search()->once()->andReturn($config['search']);
	}

	protected function configureHTML($config) {
		if(! key_exists('html', $config)) {
			return;
		}
		$this->tests->expects()->is_feed_uri()->andReturn($config['html']['is_feed']);
		if(key_exists('is_rest', $config['html']) ) {
			define( 'REST_REQUEST', true );
		}
		if(!$config['html']['is_feed'] && !key_exists('is_rest', $config['html'])) {
			$this->tests->expects()->is_html($config['buffer'])->andReturn($config['html']['is_html']);
		}
	}
}
