<?php

/**
 * Class TaskScheduler_wpPostJobStore_Test
 * @group stores
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

	public function test_cancel_job() {
		$time = new DateTime();
		$schedule = new TaskScheduler_SimpleSchedule($time);
		$job = new TaskScheduler_Job('my_hook', array(), $schedule, 'my_group');
		$store = new TaskScheduler_wpPostJobStore();
		$job_id = $store->save_job($job);
		$store->cancel_job( $job_id );

		$fetched = $store->fetch_job( $job_id );
		$this->assertInstanceOf( 'TaskScheduler_NullJob', $fetched );
	}

	public function test_claim_jobs() {
		$created_jobs = array();
		$store = new TaskScheduler_wpPostJobStore();
		for ( $i = 3 ; $i > -3 ; $i-- ) {
			$time = new DateTime($i.' hours');
			$schedule = new TaskScheduler_SimpleSchedule($time);
			$job = new TaskScheduler_Job('my_hook', array($i), $schedule, 'my_group');
			$created_jobs[] = $store->save_job($job);
		}

		$claim = $store->stake_claim();
		$this->assertInstanceof( 'TaskScheduler_JobClaim', $claim );

		$this->assertCount( 3, $claim->get_jobs() );
		$this->assertEqualSets( array_slice( $created_jobs, 3, 3 ), $claim->get_jobs() );
	}

	public function test_duplicate_claim() {
		$created_jobs = array();
		$store = new TaskScheduler_wpPostJobStore();
		for ( $i = 0 ; $i > -3 ; $i-- ) {
			$time = new DateTime($i.' hours');
			$schedule = new TaskScheduler_SimpleSchedule($time);
			$job = new TaskScheduler_Job('my_hook', array($i), $schedule, 'my_group');
			$created_jobs[] = $store->save_job($job);
		}

		$claim1 = $store->stake_claim();
		$claim2 = $store->stake_claim();
		$this->assertCount( 3, $claim1->get_jobs() );
		$this->assertCount( 0, $claim2->get_jobs() );
	}

	public function test_release_claim() {
		$created_jobs = array();
		$store = new TaskScheduler_wpPostJobStore();
		for ( $i = 0 ; $i > -3 ; $i-- ) {
			$time = new DateTime($i.' hours');
			$schedule = new TaskScheduler_SimpleSchedule($time);
			$job = new TaskScheduler_Job('my_hook', array($i), $schedule, 'my_group');
			$created_jobs[] = $store->save_job($job);
		}

		$claim1 = $store->stake_claim();

		$store->release_claim( $claim1 );

		$claim2 = $store->stake_claim();
		$this->assertCount( 3, $claim2->get_jobs() );
	}

	public function test_search() {
		$created_jobs = array();
		$store = new TaskScheduler_wpPostJobStore();
		for ( $i = -3 ; $i <= 3 ; $i++ ) {
			$time = new DateTime($i.' hours');
			$schedule = new TaskScheduler_SimpleSchedule($time);
			$job = new TaskScheduler_Job('my_hook', array($i), $schedule, 'my_group');
			$created_jobs[] = $store->save_job($job);
		}

		$next_no_args = $store->find_job( 'my_hook' );
		$this->assertEquals( $created_jobs[0], $next_no_args );

		$next_with_args = $store->find_job( 'my_hook', array( 'args' => array( 1 ) ) );
		$this->assertEquals( $created_jobs[4], $next_with_args );

		$non_existent = $store->find_job( 'my_hook', array( 'args' => array( 17 ) ) );
		$this->assertNull( $non_existent );
	}

	public function test_search_by_group() {
		$store = new TaskScheduler_wpPostJobStore();
		$schedule = new TaskScheduler_SimpleSchedule(new DateTime('tomorrow'));
		$abc = $store->save_job(new TaskScheduler_Job('my_hook', array(1), $schedule, 'abc'));
		$def = $store->save_job(new TaskScheduler_Job('my_hook', array(1), $schedule, 'def'));
		$ghi = $store->save_job(new TaskScheduler_Job('my_hook', array(1), $schedule, 'ghi'));

		$this->assertEquals( $abc, $store->find_job('my_hook', array('group' => 'abc')));
		$this->assertEquals( $def, $store->find_job('my_hook', array('group' => 'def')));
		$this->assertEquals( $ghi, $store->find_job('my_hook', array('group' => 'ghi')));
	}
}
 