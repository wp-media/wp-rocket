<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\Queue\PreloadQueueRunner;

use ActionScheduler_AsyncRequest_QueueRunner;
use Mockery;
use WP_Rocket\Engine\Common\Queue\PreloadQueueRunner;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;

class Test_Init extends TestCase
{
	protected $queueRunner;

	protected function setUp(): void
	{
		parent::setUp();
		\Mockery::mock('alias:'. ActionScheduler_AsyncRequest_QueueRunner::class);
		$this->queueRunner = new PreloadQueueRunner();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config) {
		Filters\expectApplied('cron_schedules')->with([$this->queueRunner, 'add_wp_cron_schedule']);
		Functions\expect('wp_next_scheduled')->ordered()->with(PreloadQueueRunner::WP_CRON_HOOK)->andReturn($config['timestamp']);
		$this->configureUnschedule($config);
		Functions\expect('wp_next_scheduled')->with([ 'WP Cron' ], PreloadQueueRunner::WP_CRON_HOOK)->andReturn($config['next_scheduled']);
		$this->configureNextShedule($config);
		Actions\expectAdded(PreloadQueueRunner::WP_CRON_HOOK)->with([$this->queueRunner, 'run']);
		Actions\expectAdded('shutdown')->with([$this->queueRunner, 'maybe_dispatch_async_request']);
		$this->queueRunner->init();
	}

	protected function configureUnschedule($config) {
		if(! $config['timestamp']) {
			return;
		}

		Functions\expect('wp_unschedule_event')->with($config['timestamp'], PreloadQueueRunner::WP_CRON_HOOK);
	}

	protected function configureNextShedule($config) {
		if($config['next_scheduled']) {
			return;
		}
		Filters\expectApplied('rocket_action_scheduler_run_schedule')->with(PreloadQueueRunner::WP_CRON_HOOK)->andReturn($config['schedule']);

		Functions\expect('wp_schedule_event')->with(Mockery::on(function($data) {
			return time() >= $data && time() - 3600 < $data;
		}), $config['schedule'],
			PreloadQueueRunner::WP_CRON_HOOK, [ 'WP Cron' ]);
	}
}
