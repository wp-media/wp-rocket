<?php

/**
 * Class TaskScheduler_Test
 */
class TaskScheduler_Test extends TaskScheduler_UnitTestCase {
	public function test_schedule_task() {
		$time = time();
		$hook = md5(rand());
		$job_id = schedule_single_task( $time, $hook );

		$store = TaskScheduler::store();
		$job = $store->fetch_job($job_id);
		$this->assertEquals( $time, $job->get_schedule()->next()->getTimestamp() );
		$this->assertEquals( $hook, $job->get_hook() );
	}

	public function test_recurring_task() {
		$time = time();
		$hook = md5(rand());
		$job_id = schedule_recurring_task( $time, HOUR_IN_SECONDS, $hook );

		$store = TaskScheduler::store();
		$job = $store->fetch_job($job_id);
		$this->assertEquals( $time, $job->get_schedule()->next()->getTimestamp() );
		$this->assertEquals( $time + HOUR_IN_SECONDS + 2, $job->get_schedule()->next(new DateTime('@'.($time + 2)))->getTimestamp());
		$this->assertEquals( $hook, $job->get_hook() );
	}

	public function test_cron_schedule() {
		$time = new DateTime('2014-01-01');
		$hook = md5(rand());
		$job_id = schedule_cron_task( $time->getTimestamp(), '0 0 10 10 *', $hook );

		$store = TaskScheduler::store();
		$job = $store->fetch_job($job_id);
		$expected_date = new DateTime('2014-10-10');
		$this->assertEquals( $expected_date->getTimestamp(), $job->get_schedule()->next()->getTimestamp() );
		$this->assertEquals( $hook, $job->get_hook() );
	}

	public function test_get_next() {
		$time = new DateTime('tomorrow');
		$hook = md5(rand());
		schedule_recurring_task( $time->getTimestamp(), HOUR_IN_SECONDS, $hook );

		$next = next_scheduled_task( $hook );

		$this->assertEquals( $time->getTimestamp(), $next );
	}

	public function test_unschedule() {
		$time = time();
		$hook = md5(rand());
		$job_id = schedule_single_task( $time, $hook );

		unschedule_task( $hook );

		$next = next_scheduled_task( $hook );
		$this->assertFalse($next);

		$store = TaskScheduler::store();
		$job = $store->fetch_job($job_id);

		$this->assertNull($job->get_schedule()->next());
		$this->assertEmpty($job->get_hook());
	}
}
 