<?php
declare(strict_types=1);

namespace WP_Rocket\tests\Integration\inc\Engine\Common\PerformanceHints\Cron\Controller;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\PerformanceHints\Cron\Controller::schedule_cleanup
 *
 * @group PerformanceHints
 */
class Test_ScheduleCleanup extends TestCase {
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'init', 'schedule_cleanup' );
	}

	public function tear_down() {
		$this->restoreWpHook( 'init' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		if ( $config['scheduled'] ) {
			wp_schedule_event( time(), 'daily', 'rocket_performance_hints_cleanup' );
		}

		do_action( 'init' );

		if ( $expected ) {
			$this->assertNotFalse( wp_next_scheduled( 'rocket_performance_hints_cleanup' ) );
		} else {
			$this->assertFalse( wp_next_scheduled( 'rocket_performance_hints_cleanup' ) );
		}
	}
}
