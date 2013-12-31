<?php

/**
 * Class TaskScheduler_wpPostJobStore_Test
 */
class TaskScheduler_wpPostJobStore_Test extends TaskScheduler_UnitTestCase {

	public function test_create_job() {
		$time = new DateTime();
		$schedule = new TaskScheduler_SimpleSchedule($time);
		$job = new TaskScheduler_Job('my_hook', array(), $schedule);
		$store = new TaskScheduler_wpPostJobStore();
		$job_id = $store->save_job($job);

		$this->assertNotEmpty($job_id);
	}

	public function test_retrieve_job() {
		$time = new DateTime();
		$schedule = new TaskScheduler_SimpleSchedule($time);
		$job = new TaskScheduler_Job('my_hook', array(), $schedule, 'my_group');
		$store = new TaskScheduler_wpPostJobStore();
		$job_id = $store->save_job($job);

		$retrieved = $store->fetch_job($job_id);
		$this->assertEquals($job->get_hook(), $retrieved->get_hook());
		$this->assertEqualSets($job->get_args(), $retrieved->get_args());
		$this->assertEquals($job->get_schedule()->next(), $retrieved->get_schedule()->next());
		$this->assertEquals($job->get_group(), $retrieved->get_group());
	}
}
 