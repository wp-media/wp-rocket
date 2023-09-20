<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Cron\Subscriber;

use Mockery;
use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Engine\Preload\Cron\Subscriber;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;

/**
 * @covers \WP_Rocket\Engine\Preload\Cron\Subscriber::add_revert_old_failed_interval
 *
 * @group Cron
 * @group Preload
 */
class Test_AddRevertOldFailedInterval extends TestCase
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
	public function testShouldDoAsExpected($config, $expected) {
		$this->stubTranslationFunctions();
		$this->settings->expects()->is_enabled()->andReturn($config['is_enabled']);
		$this->assertSame($expected, $this->subscriber->add_revert_old_failed_interval($config['schedules']));
	}

	public function configureInterval($config) {
		if(! $config['is_enabled'] ) {
			return;
		}
		Filters\expectApplied('rocket_preload_revert_old_failed_rows_cron_interval')->with()->andReturn($config['filtered_interval']);
	}
}
