<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Cron\Subscriber;

use WP_Rocket\Engine\Common\Queue\PreloadQueueRunner;
use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Engine\Preload\Cron\Subscriber;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;
use Mockery;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Cron\Subscriber::schedule_revert_old_in_progress_rows
 *
 * @group Cron
 * @group Preload
 */
class Test_ScheduleRevertOldFailedRows extends TestCase
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

		$this->subscriber =  new Subscriber($this->settings, $this->query, $this->controller);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config) {

		$this->settings->shouldReceive('is_enabled')->andReturn($config['is_enabled'])->atLeast()->once();
		$this->configureCheckNextSchedule($config);
		$this->configureClearSchedule($config);
		$this->configureNextSchedule($config);
		$this->configureScheduleEvent($config);
		$this->subscriber->schedule_revert_old_failed_rows();
	}

	protected function configureCheckNextSchedule($config) {
		if($config['is_enabled'] ) {
			return;
		}

		Functions\expect('wp_next_scheduled')->with('rocket_preload_revert_old_failed_rows')->andReturn($config['has_next_schedule']);
	}

	protected function configureClearSchedule($config) {
		if($config['is_enabled'] || ! $config['has_next_schedule']) {
			return;
		}

		Functions\expect('wp_clear_scheduled_hook')->with('rocket_preload_revert_old_failed_rows');
	}

	protected function configureNextSchedule($config) {
		if(! $config['is_enabled']) {
			return;
		}

		Functions\expect('wp_next_scheduled')->with('rocket_preload_revert_old_failed_rows')->andReturn($config['next_success']);
	}

	protected function configureScheduleEvent($config) {

		if(! $config['is_enabled']) {
			return;
		}

		if($config['next_success']) {
			return;
		}

		$old_time = time() + MINUTE_IN_SECONDS;

		Functions\expect('wp_schedule_event')->with( Mockery::on(function ($date) use ($old_time) {
			return $date >= $old_time  && $date <= time() + MINUTE_IN_SECONDS;
		}), 'rocket_revert_old_failed_rows', 'rocket_preload_revert_old_failed_rows');
	}
}
