<?php

/**
 * Class TaskScheduler_NullJob_Test
 * @group jobs
 */
class TaskScheduler_NullJob_Test extends TaskScheduler_UnitTestCase {
	public function test_null_job() {
		$job = new TaskScheduler_NullJob();

		$this->assertEmpty($job->get_hook());
		$this->assertEmpty($job->get_args());
		$this->assertNull($job->get_schedule()->next());
	}
}
 