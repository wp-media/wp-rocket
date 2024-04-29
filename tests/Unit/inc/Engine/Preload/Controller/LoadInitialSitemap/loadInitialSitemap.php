<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Controller\LoadInitialSitemap;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Preload\Controller\CrawlHomepage;
use WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Sitemaps;
use WP_Sitemaps_Index;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap::load_initial_sitemap
 * @group  Preload
 */
class Test_LoadInitialSitemap extends TestCase {
	protected $queue;
	protected $query;
	protected $crawler;
	protected $controller;

	protected function setUp(): void
	{
		parent::setUp();
		$this->queue = Mockery::mock(Queue::class);
		$this->query = $this->createMock(Cache::class);
		$this->crawler = Mockery::mock(CrawlHomepage::class);
		$this->controller = new LoadInitialSitemap($this->queue, $this->query, $this->crawler);
	}
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		Filters\expectApplied('rocket_sitemap_preload_list')->with($config['sitemaps'])->andReturn($config['filter_sitemaps']);
		Filters\expectApplied('rocket_preload_load_custom_urls')->with([])->andReturn([]);
		$this->queue->expects()->add_job_preload_job_preload_url_async($config['home_url']);
		foreach ($config['filter_sitemaps'] as $sitemap) {
			$this->queue->expects()->add_job_preload_job_parse_sitemap_async($sitemap);
		}
		if(count($config['filter_sitemaps']) > 0) {
			$this->queue->expects()->add_job_preload_job_check_finished_async();
		}
		if(key_exists('transient', $expected)) {
			Functions\expect('set_transient')->with('wpr_preload_running', true);
		}

		Functions\when('home_url')->justReturn($config['home_url']);
		$this->configureWordPressSitemap($config);
		$this->controller->load_initial_sitemap();
	}

	protected function configureWordPressSitemap($config) {
		if(count($config['filter_sitemaps']) > 0) {
			return ;
			$this->query->expects(self::once())->method('create_or_nothing')->with([
				'url' => $config['home_url']
			]);
		}
		Functions\expect('get_option')->with('blog_public')->andReturn($config['is_sitemap_activated']);

		if($config['is_sitemap_activated']) {
			$mock = Mockery::mock(WP_Sitemaps_Index::class);
			$mock->expects()->get_index_url()->andReturn($config['wp_sitemap']);
			$sitemap = (object) ['index' => $mock];
			Functions\expect('wp_sitemaps_get_server')->with()->andReturn($sitemap);

			$this->queue->expects()->add_job_preload_job_parse_sitemap_async($config['wp_sitemap']);
			$this->queue->expects()->add_job_preload_job_check_finished_async();
			$this->query->expects(self::once())->method('create_or_nothing')->with([
				'url' => $config['home_url']
			]);
		} else {
			$this->crawler->expects()->crawl()->andReturn($config['crawl_urls']);
			$this->query->expects(self::atLeast(1))->method('create_or_nothing')->withConsecutive(...$config['add_crawl_urls']);
		}
	}
}
