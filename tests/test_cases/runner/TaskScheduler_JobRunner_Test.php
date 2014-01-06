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
}
 