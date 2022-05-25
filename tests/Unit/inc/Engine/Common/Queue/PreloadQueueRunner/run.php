<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Controller\PreloadUrl;

use ActionScheduler_AsyncRequest_QueueRunner;
use ActionScheduler_Compatibility;
use ActionScheduler_Lock;
use ActionScheduler_QueueCleaner;
use Mockery;
use WP_Rocket\Engine\Common\Queue\PreloadQueueRunner;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;

class Test_Run extends TestCase
{
	protected $queueRunner;
	protected $store;
	protected $monitor;
	protected $cleaner;
	protected $async_request;
	protected $compatibility;
	protected $logger;
	protected $locker;

	protected function setUp(): void
	{
		parent::setUp();
		$this->store = Mockery::mock(\ActionScheduler_Store::class);
		$this->monitor = Mockery::mock(\ActionScheduler_FatalErrorMonitor::class);
		$this->async_request = Mockery::mock(ActionScheduler_AsyncRequest_QueueRunner::class);
		$this->compatibility = Mockery::mock(ActionScheduler_Compatibility::class);
		$this->cleaner = Mockery::mock(ActionScheduler_QueueCleaner::class);
		$this->logger = Mockery::mock(Logger::class);
		$this->locker = Mockery::mock(ActionScheduler_Lock::class);
		$this->queueRunner = Mockery::mock(PreloadQueueRunner::class . '[run_cleanup,get_time_limit,has_maximum_concurrent_batches,do_batch,batch_limits_exceeded]', [
			$this->store,
			$this->monitor,
			$this->cleaner,
			$this->async_request,
			$this->compatibility,
			$this->logger,
			$this->locker,
		]);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		Actions\expectDone('action_scheduler_before_process_queue');
		$this->compatibility->expects()->raise_memory_limit();
		$this->compatibility->expects()->raise_time_limit($config['time_limit']);
		$this->queueRunner->expects()->run_cleanup();
		$this->queueRunner->expects()->get_time_limit()->andReturn($config['time_limit']);
		$this->queueRunner->shouldReceive('has_maximum_concurrent_batches')->andReturnValues($config['has_max'])
			->atLeast()->once();
		Actions\expectDone('action_scheduler_after_process_queue');
		$this->configureBatch($config);
		$this->assertSame($expected, $this->queueRunner->run($config['context']));
	}

	protected function configureBatch($config) {
		if(! $config['do_batch']) {
			return;
		}
		Filters\expectApplied('action_scheduler_queue_runner_batch_size')->with(25)->andReturn($config['batch_size']);
		$this->queueRunner->shouldReceive('do_batch')->with($config['batch_size'], $config['context'])->andReturn($config['processed'])->atLeast()->once();
	}
}
