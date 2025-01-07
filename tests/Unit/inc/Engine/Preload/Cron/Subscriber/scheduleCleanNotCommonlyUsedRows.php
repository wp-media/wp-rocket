<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Cron\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Engine\Preload\Cron\Subscriber;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Cron\Subscriber::schedule_clean_not_commonly_used_rows
 *
 * @group Cron
 * @group Preload
 */
class TestScheduleCleanNotCommonlyUsedRows extends TestCase {
	protected $subscriber;
	protected $query;
	protected $settings;
	protected $controller;

	protected function setUp(): void {
		parent::setUp();

		$this->query        = $this->createMock( Cache::class );
		$this->settings     = Mockery::mock( Settings::class );
		$this->controller   = Mockery::mock( PreloadUrl::class );
		$this->subscriber   = new Subscriber( $this->settings, $this->query, $this->controller );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config ) {
		Functions\expect( 'wp_next_scheduled' )
			->once()
			->with( 'rocket_preload_clean_rows_time_event' )
			->andReturn( $config['has_next_schedule'] );

		$old_time = time() + 10 * MINUTE_IN_SECONDS;

		if ( ! $config['has_next_schedule'] ) {
			Functions\expect( 'wp_schedule_event' )
			->once()
			->with(
				Mockery::on(function ($date) use ($old_time) {
					return $date >= $old_time && $date <= time() + 10 * MINUTE_IN_SECONDS;
				} ),
				'weekly',
				'rocket_preload_clean_rows_time_event'
			);
		} else {
			Functions\expect( 'wp_schedule_event' )->never();
		}

		$this->subscriber->schedule_clean_not_commonly_used_rows();
	}
}
