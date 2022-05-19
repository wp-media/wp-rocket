<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\Queue\PreloadQueueRunner;

use ActionScheduler_ActionClaim;
use ActionScheduler_AsyncRequest_QueueRunner;
use ActionScheduler_FatalErrorMonitor;
use ActionScheduler_Store;
use Mockery;
use WP_Rocket\Engine\Common\Queue\PreloadQueueRunner;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

class Test_AddWpCronSchedule extends TestCase
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
	public function testShouldDoAsExpected($config, $expected) {
		Functions\when('__')->returnArg();
		$this->assertSame($expected, $this->queueRunner->add_wp_cron_schedule($config));
	}
}
