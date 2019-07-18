<?php

/**
 * Class ActionScheduler_CronSchedule_Test
 * @group schedules
 */
class ActionScheduler_CronSchedule_Test extends ActionScheduler_UnitTestCase {
	public function test_creation() {
		$time = as_get_datetime_object('tomorrow');
		$cron = CronExpression::factory('@daily');
		$start = clone $time;
		$start->modify( '-1 hour' );
		$schedule = new ActionScheduler_CronSchedule( $start, $cron );
		$this->assertEquals( $time, $schedule->get_date() );
		$this->assertEquals( $start, $schedule->get_first_date() );

		// Test delaying for a future start date
		$start->modify( '+1 week' );
		$time->modify( '+1 week' );

		$schedule = new ActionScheduler_CronSchedule( $start, $cron );
		$this->assertEquals( $time, $schedule->get_date() );
		$this->assertEquals( $start, $schedule->get_first_date() );
	}

	public function test_creation_with_first_date() {
		$time = as_get_datetime_object( 'tomorrow' );
		$cron = CronExpression::factory( '@daily' );
		$start = clone $time;
		$start->modify( '-1 hour' );
		$schedule = new ActionScheduler_CronSchedule( $start, $cron );
		$this->assertEquals( $time, $schedule->get_date() );
		$this->assertEquals( $start, $schedule->get_first_date() );

		// Test delaying for a future start date
		$first = clone $time;
		$first->modify( '-1 day' );
		$start->modify( '+1 week' );
		$time->modify( '+1 week' );

		$schedule = new ActionScheduler_CronSchedule( $start, $cron, $first );
		$this->assertEquals( $time, $schedule->get_date() );
		$this->assertEquals( $first, $schedule->get_first_date() );
	}

	public function test_next() {
		$time = as_get_datetime_object('2013-06-14');
		$cron = CronExpression::factory('@daily');
		$schedule = new ActionScheduler_CronSchedule($time, $cron);
		$this->assertEquals( as_get_datetime_object('tomorrow'), $schedule->get_next( as_get_datetime_object() ) );
	}

	public function test_is_recurring() {
		$schedule = new ActionScheduler_CronSchedule(as_get_datetime_object('2013-06-14'), CronExpression::factory('@daily'));
		$this->assertTrue( $schedule->is_recurring() );
	}

	public function test_cron_format() {
		$time = as_get_datetime_object('2014-01-01');
		$cron = CronExpression::factory('0 0 10 10 *');
		$schedule = new ActionScheduler_CronSchedule($time, $cron);
		$this->assertEquals( as_get_datetime_object('2014-10-10'), $schedule->get_date() );

		$cron = CronExpression::factory('0 0 L 1/2 *');
		$schedule = new ActionScheduler_CronSchedule($time, $cron);
		$this->assertEquals( as_get_datetime_object('2014-01-31'), $schedule->get_date() );
		$this->assertEquals( as_get_datetime_object('2014-07-31'), $schedule->get_next( as_get_datetime_object('2014-06-01') ) );
		$this->assertEquals( as_get_datetime_object('2028-11-30'), $schedule->get_next( as_get_datetime_object('2028-11-01') ) );

		$cron = CronExpression::factory('30 14 * * MON#3 *');
		$schedule = new ActionScheduler_CronSchedule($time, $cron);
		$this->assertEquals( as_get_datetime_object('2014-01-20 14:30:00'), $schedule->get_date() );
		$this->assertEquals( as_get_datetime_object('2014-05-19 14:30:00'), $schedule->get_next( as_get_datetime_object('2014-05-01') ) );
	}
}
 