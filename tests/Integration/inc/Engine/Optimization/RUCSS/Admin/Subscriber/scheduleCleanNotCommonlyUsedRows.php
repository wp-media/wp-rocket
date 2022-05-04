<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::schedule_clean_not_commonly_used_rows
 *
 * @group  RUCSS
 */
class Test_ScheduleCleanNotCommonlyUsedRows extends TestCase{

	public function tear_down() : void {
		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		wp_clear_scheduled_hook( 'rocket_rucss_clean_rows_time_event' );
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input ){
		$this->input = $input;
		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		do_action( 'init' );

		if ( $this->input['remove_unused_css'] ) {
			$this->assertNotFalse( wp_next_scheduled( 'rocket_rucss_clean_rows_time_event' ) );
		} else {
			$this->assertFalse( wp_next_scheduled( 'rocket_rucss_clean_rows_time_event' ) );
		}
	}

	public function set_rucss_option() {
		return $this->input['remove_unused_css'] ?? false;
	}
}
