<?php

use WP_Rocket\Engine\Preload\Controller\CrawlHomepage;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;

class Test_Crawl extends TestCase
{
	protected $controller;

	protected function setUp(): void
	{
		parent::setUp();
		$this->controller = new CrawlHomepage();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		Filters\expectApplied('https_local_ssl_verify')->with(false)->andReturn(false);
		Filters\expectApplied('rocket_homepage_preload_url_request_args')->with($config['args'])->andReturn($config['args']);
		Functions\expect('home_url')->andReturn($config['home_url']);
		Functions\expect('esc_url_raw')->with()->andReturn($config['escaped_home_url']);
		Functions\expect('wp_remote_get')->with($config['escaped_home_url'])->andReturn($config['request']['response']);
		Functions\expect('is_wp_error')->with($config['request']['response'])->andReturn($config['request']['is_error']);
		Functions\when('wp_parse_url')->alias(function ($url, $component = -1) {
			return parse_url($url, $component);
		});
		$this->configureCheckCode($config);
		$this->configureRetrieveBody($config);

		$this->assertSame($expected, $this->controller->crawl());
	}

	protected function configureCheckCode($config) {
		if($config['request']['is_error']) {
			return;
		}

		Functions\expect('wp_remote_retrieve_response_code')->with($config['request']['response'])->andReturn($config['request']['code']);
	}

	protected function configureRetrieveBody($config) {
		if($config['request']['is_error'] || $config['request']['code'] !== 200) {
			return;
		}

		Functions\expect('wp_remote_retrieve_body')->with($config['request']['response'])->andReturn($config['request']['body']);
	}
}
