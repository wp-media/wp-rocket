<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Controller\PreloadUrl;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\RocketCache;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;

class Test_processPendingJobs extends TestCase
{
	protected $queue;
	protected $query;
	protected $options;
	protected $controller;

	protected function setUp(): void
	{
		parent::setUp();
		$this->options = \Mockery::mock(Options_Data::class);
		$this->query = $this->createMock(RocketCache::class);
		$this->queue = \Mockery::mock(Queue::class);
		$this->controller = new PreloadUrl($this->options, $this->queue, $this->query);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {

		Filters\expectApplied('rocket_preload_cache_pending_jobs_cron_rows_count')->with(100)->andReturn($config['rows']);
		$this->query->expects(self::once())->method('get_pending_jobs')->with($config['rows'])->willReturn($config['jobs']);
		$this->query->expects(self::atLeastOnce())->method('make_status_inprogress')->withConsecutive(...$expected['job_ids']);
		foreach ($expected['job_urls'] as $url) {
			$this->queue->expects()->add_job_preload_job_preload_url_async( $url );
		}

		$this->controller->process_pending_jobs();
	}
}
