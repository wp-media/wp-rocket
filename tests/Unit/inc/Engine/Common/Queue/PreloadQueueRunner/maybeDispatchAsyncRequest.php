<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\Queue\PreloadQueueRunner;

use ActionScheduler_Compatibility;
use ActionScheduler_Lock;
use ActionScheduler_QueueCleaner;
use Mockery;
use Mockery\Mock;
use WP_Rocket\Engine\Common\Queue\PreloadQueueRunner;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Unit\TestCase;
use ActionScheduler_AsyncRequest_QueueRunner;
use Brain\Monkey\Functions;

class Test_MaybeDispatchAsyncRequest extends TestCase
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
		$this->queueRunner = new PreloadQueueRunner($this->store,
			$this->monitor,
			$this->cleaner,
			$this->async_request,
			$this->compatibility,
			$this->logger,
			$this->locker
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config) {
		Functions\expect('is_admin')->andReturn($config['is_admin']);
		if($config['is_admin']) {
			$this->locker->expects()->is_locked('async-request-runner')->andReturn($config['is_locked']);
		}
		if($config['is_admin'] && ! $config['is_locked']) {
			$this->locker->expects()->set('async-request-runner');
			$this->async_request->expects()->maybe_dispatch();
		}

		$this->queueRunner->maybe_dispatch_async_request();
	}
}
