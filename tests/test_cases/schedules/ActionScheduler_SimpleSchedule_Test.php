<?php

/**
 * Class ActionScheduler_SimpleSchedule_Test
 * @group schedules
 */
class ActionScheduler_SimpleSchedule_Test extends ActionScheduler_UnitTestCase {
	public function test_creation() {
		$time = new DateTime();
		$schedule = new ActionScheduler_SimpleSchedule($time);
		$this->assertEquals( $time, $schedule->next() );
	}

	public function test_past_date() {
		$time = new DateTime('-1 day');
		$schedule = new ActionScheduler_SimpleSchedule($time);
		$this->assertEquals( $time, $schedule->next() );
	}

	public function test_future_date() {
		$time = new DateTime('+1 day');
		$schedule = new ActionScheduler_SimpleSchedule($time);
		$this->assertEquals( $time, $schedule->next() );
	}

	public function test_grace_period_for_next() {
		$time = new DateTime('3 seconds ago');
		$schedule = new ActionScheduler_SimpleSchedule($time);
		$this->assertEquals( $time, $schedule->next() );
	}
}
 