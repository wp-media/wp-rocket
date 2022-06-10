<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Controller\LoadInitialSitemap;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Sitemaps_Index;

/**
 * @covers \WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap::load_initial_sitemap
 * @group  Preload
 */
class Test_LoadInitialSitemap extends TestCase {
	protected $queue;
	protected $query;
	protected $controller;

	protected function setUp(): void
	{
		parent::setUp();
		$this->queue = Mockery::mock(Queue::class);
		$this->query = $this->createMock(Cache::class);
		$this->controller = new LoadInitialSitemap($this->queue, $this->query);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		Filters\expectApplied('rocket_sitemap_preload_list')->with($config['sitemaps'])->andReturn($config['filter_sitemaps']);
		foreach ($config['filter_sitemaps'] as $sitemap) {
			$this->queue->expects()->add_job_preload_job_parse_sitemap_async($sitemap);
		}
		if(count($config['filter_sitemaps']) > 0) {
			$this->queue->expects()->add_job_preload_job_check_finished_async();
		}
		if(key_exists('transient', $expected)) {
			Functions\expect('set_transient')->with('wpr_preload_running', true);
		}
		$this->configureWordPressSitemap($config);
		$this->controller->load_initial_sitemap();
	}

	protected function configureWordPressSitemap($config) {
		if(count($config['filter_sitemaps']) > 0) {
			return ;
		}

		$mock = Mockery::mock(WP_Sitemaps_Index::class);
		$mock->expects()->get_index_url()->andReturn($config['wp_sitemap']);
		$sitemap = (object) ['index' => $mock];


		Functions\expect('wp_sitemaps_get_server')->with()->andReturn($sitemap);
		if($config['wp_sitemap']) {
			$this->queue->expects()->add_job_preload_job_parse_sitemap_async($config['wp_sitemap']);
			$this->queue->expects()->add_job_preload_job_check_finished_async();
		}
	}
}
