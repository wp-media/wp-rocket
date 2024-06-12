<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\JobManager\Cron\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\JobManager\Cron\Subscriber::schedule_pending_jobs
 *
 * @group  JobManager
 */
class Test_SchedulePendingJobs extends TestCase {
	private $rucss;

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'init', 'schedule_pending_jobs' );
	}

	public function tear_down() {
		$this->restoreWpHook( 'init' );

		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		wp_clear_scheduled_hook( 'rocket_saas_pending_jobs' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->rucss = $config['remove_unused_css'];

		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		if ( $config['scheduled'] ) {
			wp_schedule_event( time(), 'rocket_saas_pending_jobs', 'rocket_saas_pending_jobs' );
		}

		do_action( 'init' );

		if ( $expected ) {
			$this->assertNotFalse( wp_next_scheduled( 'rocket_saas_pending_jobs' ) );
		} else {
			$this->assertFalse( wp_next_scheduled( 'rocket_saas_pending_jobs' ) );
		}
	}

	public function set_rucss_option() {
		return $this->rucss;
	}
}
