<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Controller\PreloadUrl;

use Mockery;
use WP_Filesystem_Direct;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Engine\Preload\Database\Queries\RocketCache;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;

/**
 * @covers \WP_Rocket\Engine\Preload\Controller\PreloadUrl::process_pending_jobs
 * @group  Preload
 */
class Test_processPendingJobs extends TestCase
{
	protected $queue;
	protected $query;
	protected $options;
	protected $controller;
	protected $file_system;

	protected function setUp(): void
	{
		parent::setUp();
		$this->options = \Mockery::mock(Options_Data::class);
		$this->query = $this->createMock(Cache::class);
		$this->queue = \Mockery::mock(Queue::class);
		$this->file_system = Mockery::mock(WP_Filesystem_Direct::class);
		$this->controller = new PreloadUrl($this->options, $this->queue, $this->query, $this->file_system);
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
