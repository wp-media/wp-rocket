<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\JobManager\Cron\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\JobManager\Cron\Subscriber::schedule_clean_not_commonly_used_rows
 *
 * @group  JobManager
 */
class Test_ScheduleCleanNotCommonlyUsedRows extends TestCase {
	private $input;

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'init', 'rocket_schedule_clean_not_commonly_used_rows' );
	}

	public function tear_down() {
		$this->restoreWpHook( 'init' );

		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		wp_clear_scheduled_hook( 'rocket_saas_clean_rows_time_event' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input ) {
		$this->input = $input;

		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		do_action( 'init' );

		if ( $this->input['remove_unused_css'] ) {
			$this->assertNotFalse( wp_next_scheduled( 'rocket_saas_clean_rows_time_event' ) );
		} else {
			$this->assertFalse( wp_next_scheduled( 'rocket_saas_clean_rows_time_event' ) );
		}
	}

	public function set_rucss_option() {
		return $this->input['remove_unused_css'] ?? false;
	}
}
