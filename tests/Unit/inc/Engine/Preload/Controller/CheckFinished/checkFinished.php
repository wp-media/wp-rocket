<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Controller\CheckFinished;

use Mockery;
use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Engine\Preload\Controller\CheckFinished;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Preload\Controller\CheckFinished::check_finished
 * @group  Preload
 */
class Test_CheckFinished extends TestCase
{
	protected $query;
	protected $queue;
	protected $settings;
	protected $controller;

	protected function setUp(): void
	{
		parent::setUp();
		$this->query = $this->createMock(Cache::class);
		$this->queue = Mockery::mock(Queue::class);
		$this->settings = Mockery::mock(Settings::class);
		$this->controller = new CheckFinished($this->settings, $this->query, $this->queue);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config) {

		$this->settings->shouldReceive('is_enabled')->andReturn($config['is_enabled'])->zeroOrMoreTimes();
		$this->queue->expects()->has_remaining_tasks()->andReturn($config['remaining'])->atLeast()->zeroOrMoreTimes();
		$this->query->expects(self::atLeast(0))->method('has_pending_jobs')->willReturn($config['have_pending']);
		$this->configureRecreateTask($config);
		$this->configureEndProcess($config);
		$this->controller->check_finished();
	}

	protected function configureEndProcess($config) {
		if($config['is_enabled'] && ($config['remaining'] || $config['have_pending'])) {
			return;
		}
		Functions\expect('delete_transient')->with('wpr_preload_running');
	}

	protected function configureRecreateTask($config) {
		if(! $config['is_enabled'] || (!$config['remaining'] && !$config['have_pending'])) {
			return;
		}
		$this->queue->expects()->add_job_preload_job_check_finished_async();
	}
}
