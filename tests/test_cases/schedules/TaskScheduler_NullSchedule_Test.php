<?php

/**
 * Class TaskScheduler_NullSchedule_Test
 */
class TaskScheduler_NullSchedule_Test extends TaskScheduler_UnitTestCase {
	public function test_null_schedule() {
		$schedule = new TaskScheduler_NullSchedule();
		$this->assertNull( $schedule->next() );
	}
}
 