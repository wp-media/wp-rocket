<?php

/**
 * Class ActionScheduler_IntervalSchedule_Test
 * @group schedules
 */
class ActionScheduler_IntervalSchedule_Test extends ActionScheduler_UnitTestCase {
	public function test_creation() {
		$time = new DateTime();
		$schedule = new ActionScheduler_IntervalSchedule($time, HOUR_IN_SECONDS);
		$this->assertEquals( $time, $schedule->next() );
	}

	public function test_next() {
		$now = time();
		$start = $now - 30;
		$schedule = new ActionScheduler_IntervalSchedule( new DateTime("@$start"), MINUTE_IN_SECONDS );
		$this->assertEquals( $start, $schedule->next()->getTimestamp() );
		$this->assertEquals( $now + MINUTE_IN_SECONDS, $schedule->next(new DateTime())->getTimestamp() );
		$this->assertEquals( $start, $schedule->next(new DateTime("@$start"))->getTimestamp() );
	}
}
 