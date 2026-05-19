<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\JobManager\Cron\Subscriber;

use WP_Rocket\Engine\Common\Queue\RUCSSQueueRunner;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\JobManager\Cron\Subscriber::initialize_rucss_queue_runner
 *
 * @group  JobManager
 */
class Test_InitializeRucssQueueRunner extends TestCase {
	private $rucss;

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'init', 'initialize_rucss_queue_runner' );

		add_filter( 'cron_schedules', [ $this, 'register_every_minute_schedule' ] );
	}

	public function tear_down() {
		$this->restoreWpHook( 'init' );

		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		remove_filter( 'rocket_rocket_insights_enabled', '__return_true' );
		remove_filter( 'cron_schedules', [ $this, 'register_every_minute_schedule' ] );

		wp_clear_scheduled_hook( RUCSSQueueRunner::WP_CRON_HOOK, [ 'WP Cron' ] );

		parent::tear_down();
	}

	public function register_every_minute_schedule( $schedules ) {
		$schedules['every_minute'] = [
			'interval' => 60,
			'display'  => 'Every minute',
		];
		return $schedules;
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->rucss = $config['remove_unused_css'];

		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		add_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		if ( $config['scheduled'] ) {
			wp_schedule_event( time(), 'every_minute', RUCSSQueueRunner::WP_CRON_HOOK, [ 'WP Cron' ] );
		}

		do_action( 'init' );

		if ( $expected ) {
			$this->assertNotFalse( wp_next_scheduled( RUCSSQueueRunner::WP_CRON_HOOK, [ 'WP Cron' ] ) );
		} else {
			$this->assertFalse( wp_next_scheduled( RUCSSQueueRunner::WP_CRON_HOOK, [ 'WP Cron' ] ) );
		}
	}

	public function set_rucss_option() {
		return $this->rucss;
	}
}
