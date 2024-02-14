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
use Brain\Monkey\Functions;

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
		$this->options = Mockery::mock(Options_Data::class);
		$this->query = $this->createMock(Cache::class);
		$this->queue = Mockery::mock(Queue::class);
		$this->file_system = Mockery::mock(WP_Filesystem_Direct::class);
		$this->controller = Mockery::mock( PreloadUrl::class . '[is_excluded_by_filter]' ,  [$this->options, $this->queue, $this->query, $this->file_system])->shouldAllowMockingProtectedMethods();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		Functions\when( 'get_transient' )
			->justReturn( $config['transient'] );
		$this->queue->expects()->get_pending_preload_actions()->andReturn([]);
		Filters\expectApplied('rocket_preload_cache_pending_jobs_cron_rows_count')->with(100)->andReturn($config['rows']);
		$this->query->expects(self::once())->method( 'get_outdated_in_progress_jobs' )->with()->willReturn($config['outdated_jobs']);
		$this->query->expects(self::atLeastOnce())->method('make_status_failed')->withConsecutive(...$expected['outdated_jobs_id']);
		$this->query->expects(self::once())->method('get_pending_jobs')->with($config['rows'])->willReturn($config['jobs']);
		$this->query->expects(self::atLeastOnce())->method('make_status_inprogress')->withConsecutive(...$expected['job_ids']);
		$this->query->expects(self::atLeast(0))->method('delete_by_url')->withConsecutive($expected['job_deleted']);
		$this->controller->shouldReceive('is_excluded_by_filter')->zeroOrMoreTimes()->andReturnValues($config['excluded']);
		foreach ($expected['job_urls'] as $url) {
			$this->queue->expects()->add_job_preload_job_preload_url_async( $url );
		}

		$this->controller->process_pending_jobs();
	}
}
