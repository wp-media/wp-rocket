<?php

/**
 * Class ActionScheduler_QueueRunner_Test
 * @group runners
 */
class ActionScheduler_QueueRunner_Test extends ActionScheduler_UnitTestCase {
	public function test_create_runner() {
		$store = ActionScheduler::store();
		$runner = ActionScheduler_Mocker::get_queue_runner( $store );
		$actions_run = $runner->run();

		$this->assertEquals( 0, $actions_run );
	}

	public function test_run() {
		$store = ActionScheduler::store();
		$runner = ActionScheduler_Mocker::get_queue_runner( $store );

		$mock = new MockAction();
		$random = md5(rand());
		add_action( $random, array( $mock, 'action' ) );
		$schedule = new ActionScheduler_SimpleSchedule(as_get_datetime_object('1 day ago'));

		for ( $i = 0 ; $i < 5 ; $i++ ) {
			$action = new ActionScheduler_Action( $random, array($random), $schedule );
			$store->save_action( $action );
		}

		$actions_run = $runner->run();

		remove_action( $random, array( $mock, 'action' ) );

		$this->assertEquals( 5, $mock->get_call_count() );
		$this->assertEquals( 5, $actions_run );
	}

	public function test_run_with_future_actions() {
		$store = ActionScheduler::store();
		$runner = ActionScheduler_Mocker::get_queue_runner( $store );

		$mock = new MockAction();
		$random = md5(rand());
		add_action( $random, array( $mock, 'action' ) );
		$schedule = new ActionScheduler_SimpleSchedule(as_get_datetime_object('1 day ago'));

		for ( $i = 0 ; $i < 3 ; $i++ ) {
			$action = new ActionScheduler_Action( $random, array($random), $schedule );
			$store->save_action( $action );
		}

		$schedule = new ActionScheduler_SimpleSchedule(as_get_datetime_object('tomorrow'));
		for ( $i = 0 ; $i < 3 ; $i++ ) {
			$action = new ActionScheduler_Action( $random, array($random), $schedule );
			$store->save_action( $action );
		}

		$actions_run = $runner->run();

		remove_action( $random, array( $mock, 'action' ) );

		$this->assertEquals( 3, $mock->get_call_count() );
		$this->assertEquals( 3, $actions_run );
	}

	public function test_completed_action_status() {
		$store = ActionScheduler::store();
		$runner = ActionScheduler_Mocker::get_queue_runner( $store );

		$random = md5(rand());
		$schedule = new ActionScheduler_SimpleSchedule(as_get_datetime_object('12 hours ago'));

		$action = new ActionScheduler_Action( $random, array(), $schedule );
		$action_id = $store->save_action( $action );

		$runner->run();

		$finished_action = $store->fetch_action( $action_id );

		$this->assertTrue( $finished_action->is_finished() );
	}

	public function test_next_instance_of_cron_action() {
		// Create an action with daily Cron expression (i.e. midnight each day)
		$random    = md5( rand() );
		$action_id = ActionScheduler::factory()->cron( $random, array(), null, '0 0 * * *' );
		$store     = ActionScheduler::store();
		$runner    = ActionScheduler_Mocker::get_queue_runner( $store );

		// Make sure the 1st instance of the action is scheduled to occur tomorrow
		$date = as_get_datetime_object( 'tomorrow' );
		$date->modify( '-1 minute' );
		$claim = $store->stake_claim( 10, $date );
		$this->assertCount( 0, $claim->get_actions() );

		$store->release_claim( $claim );

		$date->modify( '+1 minute' );

		$claim = $store->stake_claim( 10, $date );
		$actions = $claim->get_actions();
		$this->assertCount( 1, $actions );

		$fetched_action_id = reset( $actions );
		$fetched_action    = $store->fetch_action( $fetched_action_id );

		$this->assertEquals( $fetched_action_id, $action_id );
		$this->assertEquals( $random, $fetched_action->get_hook() );
		$this->assertEquals( $date->getTimestamp(), $fetched_action->get_schedule()->get_date()->getTimestamp(), '', 1 );

		$store->release_claim( $claim );

		// Make sure the 2nd instance of the cron action is scheduled to occur tomorrow still
		$runner->process_action( $action_id );

		$claim = $store->stake_claim( 10, $date );
		$actions = $claim->get_actions();
		$this->assertCount( 1, $actions );

		$fetched_action_id = reset( $actions );
		$fetched_action    = $store->fetch_action( $fetched_action_id );

		$this->assertNotEquals( $fetched_action_id, $action_id );
		$this->assertEquals( $random, $fetched_action->get_hook() );
		$this->assertEquals( $date->getTimestamp(), $fetched_action->get_schedule()->get_date()->getTimestamp(), '', 1 );
	}

	public function test_next_instance_of_interval_action() {
		// Create an action to recur every 24 hours, with the first instance scheduled to run 12 hours ago
		$random    = md5( rand() );
		$date      = as_get_datetime_object( '12 hours ago' );
		$action_id = ActionScheduler::factory()->recurring( $random, array(), $date->getTimestamp(), DAY_IN_SECONDS );
		$store     = ActionScheduler::store();
		$runner    = ActionScheduler_Mocker::get_queue_runner( $store );

		// Make sure the 1st instance of the action is scheduled to occur 12 hours ago
		$claim = $store->stake_claim( 10, $date );
		$actions = $claim->get_actions();
		$this->assertCount( 1, $actions );

		$fetched_action_id = reset( $actions );
		$fetched_action    = $store->fetch_action( $fetched_action_id );

		$this->assertEquals( $fetched_action_id, $action_id );
		$this->assertEquals( $random, $fetched_action->get_hook() );
		$this->assertEquals( $date->getTimestamp(), $fetched_action->get_schedule()->get_date()->getTimestamp(), '', 1 );

		$store->release_claim( $claim );

		// Make sure after the queue is run, the 2nd instance of the action is scheduled to occur in 24 hours
		$runner->run();

		$date = as_get_datetime_object( '+1 day' );
		$claim = $store->stake_claim( 10, $date );
		$actions = $claim->get_actions();
		$this->assertCount( 1, $actions );

		$fetched_action_id = reset( $actions );
		$fetched_action    = $store->fetch_action( $fetched_action_id );

		$this->assertNotEquals( $fetched_action_id, $action_id );
		$this->assertEquals( $random, $fetched_action->get_hook() );
		$this->assertEquals( $date->getTimestamp(), $fetched_action->get_schedule()->get_date()->getTimestamp(), '', 1 );

		$store->release_claim( $claim );

		// Make sure the 3rd instance of the cron action is scheduled for 24 hours from now, as the action was run early, ahead of schedule
		$runner->process_action( $action_id );
		$date = as_get_datetime_object( '+1 day' );

		$claim = $store->stake_claim( 10, $date );
		$actions = $claim->get_actions();
		$this->assertCount( 1, $actions );

		$fetched_action_id = reset( $actions );
		$fetched_action    = $store->fetch_action( $fetched_action_id );

		$this->assertNotEquals( $fetched_action_id, $action_id );
		$this->assertEquals( $random, $fetched_action->get_hook() );
		$this->assertEquals( $date->getTimestamp(), $fetched_action->get_schedule()->get_date()->getTimestamp(), '', 1 );
	}

	public function test_hooked_into_wp_cron() {
		$next = wp_next_scheduled( ActionScheduler_QueueRunner::WP_CRON_HOOK, array( 'WP Cron' ) );
		$this->assertNotEmpty($next);
	}

	public function test_batch_count_limit() {
		$store = ActionScheduler::store();
		$runner = ActionScheduler_Mocker::get_queue_runner( $store );

		$mock = new MockAction();
		$random = md5(rand());
		add_action( $random, array( $mock, 'action' ) );
		$schedule = new ActionScheduler_SimpleSchedule(new ActionScheduler_DateTime('1 day ago'));

		for ( $i = 0 ; $i < 2 ; $i++ ) {
			$action = new ActionScheduler_Action( $random, array($random), $schedule );
			$store->save_action( $action );
		}

		$claim = $store->stake_claim();

		$actions_run = $runner->run();

		$this->assertEquals( 0, $mock->get_call_count() );
		$this->assertEquals( 0, $actions_run );

		$store->release_claim( $claim );

		$actions_run = $runner->run();

		$this->assertEquals( 2, $mock->get_call_count() );
		$this->assertEquals( 2, $actions_run );

		remove_action( $random, array( $mock, 'action' ) );
	}

	public function test_changing_batch_count_limit() {
		$store = ActionScheduler::store();
		$runner = ActionScheduler_Mocker::get_queue_runner( $store );

		$random = md5(rand());
		$schedule = new ActionScheduler_SimpleSchedule(new ActionScheduler_DateTime('1 day ago'));

		for ( $i = 0 ; $i < 30 ; $i++ ) {
			$action = new ActionScheduler_Action( $random, array($random), $schedule );
			$store->save_action( $action );
		}

		$claims = array();

		for ( $i = 0 ; $i < 5 ; $i++ ) {
			$claims[] = $store->stake_claim( 5 );
		}

		$mock1 = new MockAction();
		add_action( $random, array( $mock1, 'action' ) );
		$actions_run = $runner->run();
		remove_action( $random, array( $mock1, 'action' ) );

		$this->assertEquals( 0, $mock1->get_call_count() );
		$this->assertEquals( 0, $actions_run );


		add_filter( 'action_scheduler_queue_runner_concurrent_batches', array( $this, 'return_6' ) );

		$mock2 = new MockAction();
		add_action( $random, array( $mock2, 'action' ) );
		$actions_run = $runner->run();
		remove_action( $random, array( $mock2, 'action' ) );

		$this->assertEquals( 5, $mock2->get_call_count() );
		$this->assertEquals( 5, $actions_run );

		remove_filter( 'action_scheduler_queue_runner_concurrent_batches', array( $this, 'return_6' ) );

		for ( $i = 0 ; $i < 5 ; $i++ ) { // to make up for the actions we just processed
			$action = new ActionScheduler_Action( $random, array($random), $schedule );
			$store->save_action( $action );
		}

		$mock3 = new MockAction();
		add_action( $random, array( $mock3, 'action' ) );
		$actions_run = $runner->run();
		remove_action( $random, array( $mock3, 'action' ) );

		$this->assertEquals( 0, $mock3->get_call_count() );
		$this->assertEquals( 0, $actions_run );

		remove_filter( 'action_scheduler_queue_runner_concurrent_batches', array( $this, 'return_6' ) );
	}

	public function return_6() {
		return 6;
	}

	public function test_store_fetch_action_failure_schedule_next_instance() {
		$random    = md5( rand() );
		$schedule  = new ActionScheduler_IntervalSchedule( as_get_datetime_object( '12 hours ago' ), DAY_IN_SECONDS );
		$action    = new ActionScheduler_Action( $random, array(), $schedule );
		$action_id = ActionScheduler::store()->save_action( $action );

		// Set up a mock store that will throw an exception when fetching actions.
		$store = $this
					->getMockBuilder( 'ActionScheduler_wpPostStore' )
					->setMethods( array( 'fetch_action' ) )
					->getMock();
		$store
			->method( 'fetch_action' )
			->with( $action_id )
			->will( $this->throwException( new Exception() ) );

		// Set up a mock queue runner to verify that schedule_next_instance()
		// isn't called for an undefined $action.
		$runner = $this
					->getMockBuilder( 'ActionScheduler_QueueRunner' )
					->setConstructorArgs( array( $store ) )
					->setMethods( array( 'schedule_next_instance' ) )
					->getMock();
		$runner
			->expects( $this->never() )
			->method( 'schedule_next_instance' );

		$runner->run();

		// Set up a mock store that will throw an exception when fetching actions.
		$store2 = $this
					->getMockBuilder( 'ActionScheduler_wpPostStore' )
					->setMethods( array( 'fetch_action' ) )
					->getMock();
		$store2
			->method( 'fetch_action' )
			->with( $action_id )
			->willReturn( null );

		// Set up a mock queue runner to verify that schedule_next_instance()
		// isn't called for an undefined $action.
		$runner2 = $this
					->getMockBuilder( 'ActionScheduler_QueueRunner' )
					->setConstructorArgs( array( $store ) )
					->setMethods( array( 'schedule_next_instance' ) )
					->getMock();
		$runner2
			->expects( $this->never() )
			->method( 'schedule_next_instance' );

		$runner2->run();
	}
}
