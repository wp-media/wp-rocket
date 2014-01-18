<?php

/**
 * Class TaskScheduler_JobRunner_Test
 * @group runners
 */
class TaskScheduler_JobRunner_Test extends TaskScheduler_UnitTestCase {
	public function test_create_runner() {
		$store = new TaskScheduler_wpPostJobStore();
		$runner = new TaskScheduler_JobRunner( $store );
		$jobs_run = $runner->run();

		$this->assertEquals( 0, $jobs_run );
	}

	public function test_run() {
		$store = new TaskScheduler_wpPostJobStore();
		$runner = new TaskScheduler_JobRunner( $store );

		$mock = new MockAction();
		$random = md5(rand());
		add_action( $random, array( $mock, 'action' ) );
		$schedule = new TaskScheduler_SimpleSchedule(new DateTime('1 day ago'));

		for ( $i = 0 ; $i < 5 ; $i++ ) {
			$job = new TaskScheduler_Job( $random, array($random), $schedule );
			$store->save_job( $job );
		}

		$jobs_run = $runner->run();

		remove_action( $random, array( $mock, 'action' ) );

		$this->assertEquals( 5, $mock->get_call_count() );
		$this->assertEquals( 5, $jobs_run );
	}

	public function test_run_with_future_tasks() {
		$store = new TaskScheduler_wpPostJobStore();
		$runner = new TaskScheduler_JobRunner( $store );

		$mock = new MockAction();
		$random = md5(rand());
		add_action( $random, array( $mock, 'action' ) );
		$schedule = new TaskScheduler_SimpleSchedule(new DateTime('1 day ago'));

		for ( $i = 0 ; $i < 3 ; $i++ ) {
			$job = new TaskScheduler_Job( $random, array($random), $schedule );
			$store->save_job( $job );
		}

		$schedule = new TaskScheduler_SimpleSchedule(new DateTime('tomorrow'));
		for ( $i = 0 ; $i < 3 ; $i++ ) {
			$job = new TaskScheduler_Job( $random, array($random), $schedule );
			$store->save_job( $job );
		}

		$jobs_run = $runner->run();

		remove_action( $random, array( $mock, 'action' ) );

		$this->assertEquals( 3, $mock->get_call_count() );
		$this->assertEquals( 3, $jobs_run );
	}

	public function test_completed_job_status() {
		$store = new TaskScheduler_wpPostJobStore();
		$runner = new TaskScheduler_JobRunner( $store );

		$random = md5(rand());
		$schedule = new TaskScheduler_SimpleSchedule(new DateTime('12 hours ago'));

		$job = new TaskScheduler_Job( $random, array(), $schedule );
		$job_id = $store->save_job( $job );

		$runner->run();

		$finished_job = $store->fetch_job( $job_id );

		$this->assertTrue( $finished_job->is_finished() );
	}

	public function test_next_instance_of_job() {
		$store = new TaskScheduler_wpPostJobStore();
		$runner = new TaskScheduler_JobRunner( $store );

		$random = md5(rand());
		$schedule = new TaskScheduler_IntervalSchedule(new DateTime('12 hours ago'), DAY_IN_SECONDS);

		$job = new TaskScheduler_Job( $random, array(), $schedule );
		$store->save_job( $job );

		$runner->run();

		$claim = $store->stake_claim(10, new DateTime((DAY_IN_SECONDS - 60).' seconds'));
		$this->assertCount(0, $claim->get_jobs());

		$claim = $store->stake_claim(10, new DateTime(DAY_IN_SECONDS.' seconds'));
		$this->assertCount(1, $claim->get_jobs());

		$job_id = reset($claim->get_jobs());
		$new_job = $store->fetch_job($job_id);


		$this->assertEquals( $random, $new_job->get_hook() );
		$this->assertEquals( $schedule->next(new DateTime()), $new_job->get_schedule()->next(new DateTime()) );
	}

	public function test_hooked_into_wp_cron() {
		$next = wp_next_scheduled( TaskScheduler_JobRunner::WP_CRON_HOOK );
		$this->assertNotEmpty($next);
	}
}
 