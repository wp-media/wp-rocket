<?php

/**
 * Class ActionScheduler_IntervalSchedule_Test
 * @group schedules
 */
class ActionScheduler_IntervalSchedule_Test extends ActionScheduler_UnitTestCase {
	public function test_creation() {
		$time = new DateTime(null, new DateTimeZone('UTC'));
		$schedule = new ActionScheduler_IntervalSchedule($time, HOUR_IN_SECONDS);
		$this->assertEquals( $time, $schedule->next() );
	}

	public function test_next() {
		$now = time();
		$start = $now - 30;
		$schedule = new ActionScheduler_IntervalSchedule( new DateTime("@$start", new DateTimeZone('UTC')), MINUTE_IN_SECONDS );
		$this->assertEquals( $start, $schedule->next()->format('U') );
		$this->assertEquals( $now + MINUTE_IN_SECONDS, $schedule->next(new DateTime(null, new DateTimeZone('UTC')))->format('U') );
		$this->assertEquals( $start, $schedule->next(new DateTime("@$start", new DateTimeZone('UTC')))->format('U') );
	}
}
 