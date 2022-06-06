<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Cron\Subscriber;

use Mockery;
use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Engine\Preload\Cron\Subscriber;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Engine\Preload\Database\Queries\RocketCache;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

class Test_ScheduleCleanNotCommonlyUsedRows extends TestCase
{
	protected $subscriber;
	protected $query;
	protected $settings;
	protected $controller;

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
		$this->settings->expects()->is_enabled()->andReturn($config['is_enabled']);
		$this->configureCheckNextSchedule($config);
		$this->configureClearSchedule($config);
		$this->configureNextSchedule($config);
		$this->configureScheduleEvent($config);
		$this->subscriber->schedule_clean_not_commonly_used_rows();
	}

	protected function configureCheckNextSchedule($config) {
		if($config['is_enabled'] ) {
			return;
		}

		Functions\expect('wp_next_scheduled')->with('rocket_load_preload_url')->andReturn($config['has_next_schedule']);
	}

	protected function configureClearSchedule($config) {
		if($config['is_enabled'] || ! $config['has_next_schedule']) {
			return;
		}

		Functions\expect('wp_clear_scheduled_hook')->with('rocket_preload_clean_rows_time_event');
	}

	protected function configureNextSchedule($config) {
		if(! $config['is_enabled'] && $config['has_next_schedule']) {
			return;
		}

		Functions\expect('wp_next_scheduled')->with('rocket_preload_clean_rows_time_event')->andReturn($config['next_success']);
	}

	protected function configureScheduleEvent($config) {
		if(! $config['is_enabled'] && $config['has_next_schedule']) {
			return;
		}

		if($config['next_success']) {
			return;
		}

		$old_time = time();

		Functions\expect('wp_schedule_event')->with( Mockery::on(function ($date) use ($old_time) {
			return $date >= $old_time  && $date <= time();
		}), 'weekly', 'rocket_preload_clean_rows_time_event');
	}
}
