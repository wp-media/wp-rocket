<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Cron\Subscriber;

use Mockery;
use WP_Rocket\Engine\Common\Queue\PreloadQueueRunner;
use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Engine\Preload\Cron\Subscriber;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Engine\Preload\Database\Queries\RocketCache;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Preload\Cron\Subscriber::schedule_clean_not_commonly_used_rows
 *
 * @group Cron
 * @group Preload
 */
class Test_ScheduleCleanNotCommonlyUsedRows extends TestCase
{
	protected $subscriber;
	protected $query;
	protected $settings;
	protected $controller;
	protected $queue_runner;

	protected function setUp(): void
	{
		parent::setUp();
		$this->query = $this->createMock(Cache::class);
		$this->settings = Mockery::mock(Settings::class);
		$this->controller = Mockery::mock(PreloadUrl::class);
		$this->queue_runner = Mockery::mock(PreloadQueueRunner::class);

		$this->subscriber =  new Subscriber($this->settings, $this->query, $this->controller, $this->queue_runner);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config) {
		$this->configureCheckNextSchedule($config);
		$this->configureScheduleEvent($config);
		$this->subscriber->schedule_clean_not_commonly_used_rows();
	}

	protected function configureCheckNextSchedule($config) {

		Functions\expect('wp_next_scheduled')->with('rocket_load_preload_url')->andReturn($config['has_next_schedule']);
	}

	protected function configureScheduleEvent($config) {
		if($config['has_next_schedule']) {
			return;
		}

		$old_time = time() + 10 * MINUTE_IN_SECONDS;

		Functions\expect('wp_schedule_event')->with( Mockery::on(function ($date) use ($old_time) {
			return $date >= $old_time  && $date <= time() + 10 * MINUTE_IN_SECONDS;
		}), 'weekly', 'rocket_preload_clean_rows_time_event');
	}
}
