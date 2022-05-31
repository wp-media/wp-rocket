<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Frontend\ParseSitemap;

use Mockery;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Engine\Preload\Frontend\ParseSitemap;
use WP_Rocket\Engine\Preload\Frontend\SitemapParser;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
/**
 * @covers \WP_Rocket\Engine\Preload\Frontend\ParseSitemap::parse_sitemap
 * @group  Preload
 */
class Test_ParseSitemap extends TestCase {
	protected $sitemap_parser;
	protected $queue;
	protected $query;
	protected $controller;

	protected function setUp(): void
	{
		parent::setUp();
		$this->sitemap_parser = Mockery::mock(SitemapParser::class);
		$this->queue = Mockery::mock(Queue::class);
		$this->query = $this->createMock(Cache::class);
		$this->controller = new ParseSitemap($this->sitemap_parser, $this->queue, $this->query);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config) {

		$this->configureRequest($config);
		$this->configureParseSitemap($config);

		$this->controller->parse_sitemap($config['url']);
	}

	protected function configureRequest($config) {
		Functions\expect('wp_remote_get')->with($config['url'])->andReturn($config['response']);
		Functions\expect('wp_remote_retrieve_response_code')->with($config['response'])->andReturn($config['status']);
		if(! key_exists('request_succeed', $config)) {
			return;
		}
		Functions\expect('wp_remote_retrieve_body')->with($config['response'])->andReturn($config['content']);
	}

	protected function configureParseSitemap($config) {
		if(! key_exists('request_succeed', $config)) {
			return;
		}
		$this->sitemap_parser->expects()->set_content($config['content']);
		$this->sitemap_parser->expects()->get_links()->andReturn($config['links']);
		$this->sitemap_parser->expects()->get_children()->andReturn($config['children']);

		foreach ($config['links'] as $index => $link) {
			$this->query->expects(self::any())->method('create_or_nothing')->withConsecutive(...$config['jobs'])
				->willReturn(true);

			$this->queue->expects()->add_job_preload_job_preload_url_async($link);

		}

		foreach ($config['children'] as $child) {
			$this->queue->expects()->add_job_preload_job_parse_sitemap_async($child);
		}
	}
}
