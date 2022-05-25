<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Admin\Subscriber;

use WP_Rocket\Engine\Common\Queue\PreloadQueueRunner;
use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Engine\Preload\Admin\Subscriber;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Preload\Admin\Subscriber::schedule_preload_pending_jobs_cron
 * @group  Preload
 */
class Test_SchedulePreloadPendingJobsCron extends TestCase
{
	protected $settings;
	protected $subscriber;
	protected $queue;
	protected $queueRunner;
	protected $logger;

	protected function setUp(): void
	{
		parent::setUp();
		$this->settings = \Mockery::mock(Settings::class);
		$this->queue = \Mockery::mock(Queue::class);
		$this->queueRunner = \Mockery::mock(PreloadQueueRunner::class);
		$this->logger = \Mockery::mock(Logger::class);
		$this->subscriber = new Subscriber($this->settings, $this->queue, $this->queueRunner, $this->logger);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config) {
		$this->settings->expects()->is_enabled()->andReturn($config['is_enable']);
		$this->configureEnable($config);
		$this->configureHasPending($config);
		$this->subscriber->schedule_preload_pending_jobs_cron();
	}

	protected function configureHasPending($config) {
		if($config['is_enable']) {
			return;
		}
		$this->queue->expects()->is_pending_jobs_cron_scheduled()->andReturn($config['is_pending']);
		if(! $config['is_pending']) {
			return;
		}
		$this->logger->expects()->debug('PRELOAD: Cancel pending jobs cron job because of disabling PRELOAD option.');
		$this->queue->expects()->cancel_pending_jobs_cron();
	}

	protected function configureEnable($config) {
		if(! $config['is_enable']) {
			return;
		}
		$this->queueRunner->expects()->init();
		$this->logger->expects()->debug("PRELOAD: Schedule pending jobs Cron job with interval {$config['interval']} seconds.");
		Functions\expect('rocket_get_constant')->with( 'MINUTE_IN_SECONDS', 60 )->andReturn($config['interval']);
		Filters\expectApplied('rocket_preload_pending_jobs_cron_interval')->with($config['interval'])->with($config['interval_filter']);
		$this->queue->expects()->schedule_pending_jobs_cron($config['interval_filter']);
	}
}
