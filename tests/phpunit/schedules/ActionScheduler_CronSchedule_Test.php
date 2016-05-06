<?php

/**
 * Class ActionScheduler_CronSchedule_Test
 * @group schedules
 */
class ActionScheduler_CronSchedule_Test extends ActionScheduler_UnitTestCase {
	public function test_creation() {
		$time = new DateTime('tomorrow', new DateTimeZone('UTC'));
		$cron = CronExpression::factory('@daily');
		$schedule = new ActionScheduler_CronSchedule($time, $cron);
		$this->assertEquals( $time, $schedule->next() );
	}

	public function test_next() {
		$time = new DateTime('2013-06-14', new DateTimeZone('UTC'));
		$cron = CronExpression::factory('@daily');
		$schedule = new ActionScheduler_CronSchedule($time, $cron);
		$this->assertEquals( new DateTime('tomorrow', new DateTimeZone('UTC')), $schedule->next( new DateTime(null, new DateTimeZone('UTC')) ) );
	}

	public function test_cron_format() {
		$time = new DateTime('2014-01-01', new DateTimeZone('UTC'));
		$cron = CronExpression::factory('0 0 10 10 *');
		$schedule = new ActionScheduler_CronSchedule($time, $cron);
		$this->assertEquals( new DateTime('2014-10-10', new DateTimeZone('UTC')), $schedule->next() );

		$cron = CronExpression::factory('0 0 L 1/2 *');
		$schedule = new ActionScheduler_CronSchedule($time, $cron);
		$this->assertEquals( new DateTime('2014-01-31', new DateTimeZone('UTC')), $schedule->next() );
		$this->assertEquals( new DateTime('2014-07-31', new DateTimeZone('UTC')), $schedule->next( new DateTime('2014-06-01', new DateTimeZone('UTC')) ) );
		$this->assertEquals( new DateTime('2028-11-30', new DateTimeZone('UTC')), $schedule->next( new DateTime('2028-11-01', new DateTimeZone('UTC')) ) );

		$cron = CronExpression::factory('30 14 * * MON#3 *');
		$schedule = new ActionScheduler_CronSchedule($time, $cron);
		$this->assertEquals( new DateTime('2014-01-20 14:30:00', new DateTimeZone('UTC')), $schedule->next() );
		$this->assertEquals( new DateTime('2014-05-19 14:30:00', new DateTimeZone('UTC')), $schedule->next( new DateTime('2014-05-01', new DateTimeZone('UTC')) ) );
	}
}
 