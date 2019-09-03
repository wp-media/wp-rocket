<?php

/**
 * Class ActionScheduler_IntervalSchedule_Test
 * @group schedules
 */
class ActionScheduler_IntervalSchedule_Test extends ActionScheduler_UnitTestCase {
	public function test_creation() {
		$time = as_get_datetime_object();
		$schedule = new ActionScheduler_IntervalSchedule($time, HOUR_IN_SECONDS);
		$this->assertEquals( $time, $schedule->get_date() );
		$this->assertEquals( $time, $schedule->get_first_date() );
	}

	public function test_creation_with_first_date() {
		$first = as_get_datetime_object();
		$time  = as_get_datetime_object( '+12 hours' );
		$schedule = new ActionScheduler_IntervalSchedule( $time, HOUR_IN_SECONDS, $first );
		$this->assertEquals( $time, $schedule->get_date() );
		$this->assertEquals( $first, $schedule->get_first_date() );
	}

	public function test_next() {
		$now = time();
		$start = $now - 30;
		$schedule = new ActionScheduler_IntervalSchedule( as_get_datetime_object("@$start"), MINUTE_IN_SECONDS );
		$this->assertEquals( $start, $schedule->get_date()->getTimestamp() );
		$this->assertEquals( $now + MINUTE_IN_SECONDS, $schedule->get_next(as_get_datetime_object())->getTimestamp() );
		$this->assertEquals( $start, $schedule->get_next( as_get_datetime_object( "@$start" ) )->getTimestamp() );
	}

	public function test_is_recurring() {
		$start = time() - 30;
		$schedule = new ActionScheduler_IntervalSchedule( as_get_datetime_object("@$start"), MINUTE_IN_SECONDS );
		$this->assertTrue( $schedule->is_recurring() );
	}
}
