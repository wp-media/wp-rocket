<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Cron\Subscriber;

use Mockery;
use WP_Rocket\Engine\Common\Queue\PreloadQueueRunner;
use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Engine\Preload\Cron\Subscriber;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Engine\Preload\Database\Tables\Cache as CacheTable;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;

/**
 * @covers \WP_Rocket\Engine\Preload\Cron\Subscriber::add_revert_old_in_progress_interval
 *
 * @group Cron
 * @group Preload
 */
class Test_AddRevertOldInProgressInterval extends TestCase
{
	protected $subscriber;
	protected $query;
	protected $settings;
	protected $controller;
	protected $queue_runner;
	protected $table;

	protected function setUp(): void
	{
		parent::setUp();
		$this->query = $this->createMock(Cache::class);
		$this->settings = Mockery::mock(Settings::class);
		$this->controller = Mockery::mock(PreloadUrl::class);
		$this->queue_runner = Mockery::mock(PreloadQueueRunner::class);
		$this->table = $this->createMock(CacheTable::class);

		$this->subscriber =  new Subscriber($this->settings, $this->query, $this->controller, $this->queue_runner, $this->table);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		$this->stubTranslationFunctions();
		$this->settings->expects()->is_enabled()->andReturn($config['is_enabled']);
		$this->assertSame($expected, $this->subscriber->add_revert_old_in_progress_interval($config['schedules']));
	}

	public function configureInterval($config) {
		if(! $config['is_enabled'] ) {
			return;
		}
		Filters\expectApplied('rocket_preload_revert_old_in_progress_rows_cron_interval')->with()->andReturn($config['filtered_interval']);
	}
}
