<?php

/**
 * Class ActionScheduler_QueueCleaner_Test
 */
class ActionScheduler_QueueCleaner_Test extends ActionScheduler_UnitTestCase {

	public function test_delete_old_actions() {
		$store = new ActionScheduler_wpPostStore();
		$runner = new ActionScheduler_QueueRunner( $store );

		$random = md5(rand());
		$schedule = new ActionScheduler_SimpleSchedule(as_get_datetime_object('1 day ago'));

		$created_actions = array();
		for ( $i = 0 ; $i < 5 ; $i++ ) {
			$action = new ActionScheduler_Action( $random, array($random), $schedule );
			$created_actions[] = $store->save_action( $action );
		}

		$runner->run();

		add_filter( 'action_scheduler_retention_period', '__return_zero' ); // delete any finished job
		$cleaner = new ActionScheduler_QueueCleaner( $store );
		$cleaner->delete_old_actions();
		remove_filter( 'action_scheduler_retention_period', '__return_zero' );

		foreach ( $created_actions as $action_id ) {
			$action = $store->fetch_action($action_id);
			$this->assertFalse($action->is_finished()); // it's a NullAction
		}
	}

	public function test_do_not_delete_recent_actions() {
		$store = new ActionScheduler_wpPostStore();
		$runner = new ActionScheduler_QueueRunner( $store );

		$random = md5(rand());
		$schedule = new ActionScheduler_SimpleSchedule(as_get_datetime_object('1 day ago'));

		$created_actions = array();
		for ( $i = 0 ; $i < 5 ; $i++ ) {
			$action = new ActionScheduler_Action( $random, array($random), $schedule );
			$created_actions[] = $store->save_action( $action );
		}

		$runner->run();

		$cleaner = new ActionScheduler_QueueCleaner( $store );
		$cleaner->delete_old_actions();

		foreach ( $created_actions as $action_id ) {
			$action = $store->fetch_action($action_id);
			$this->assertTrue($action->is_finished()); // It's a FinishedAction
		}
	}

	public function test_reset_unrun_actions() {
		$store = new ActionScheduler_wpPostStore();

		$random = md5(rand());
		$schedule = new ActionScheduler_SimpleSchedule(as_get_datetime_object('1 day ago'));

		$created_actions = array();
		for ( $i = 0 ; $i < 5 ; $i++ ) {
			$action = new ActionScheduler_Action( $random, array($random), $schedule );
			$created_actions[] = $store->save_action( $action );
		}

		$store->stake_claim(10);

		// don't actually process the jobs, to simulate a request that timed out

		add_filter( 'action_scheduler_timeout_period', '__return_zero' ); // delete any finished job
		$cleaner = new ActionScheduler_QueueCleaner( $store );
		$cleaner->reset_timeouts();

		remove_filter( 'action_scheduler_timeout_period', '__return_zero' );

		$claim = $store->stake_claim(10);
		$this->assertEqualSets($created_actions, $claim->get_actions());
	}

	public function test_do_not_reset_failed_action() {
		$store = new ActionScheduler_wpPostStore();

		$random = md5(rand());
		$schedule = new ActionScheduler_SimpleSchedule(as_get_datetime_object('1 day ago'));

		$created_actions = array();
		for ( $i = 0 ; $i < 5 ; $i++ ) {
			$action = new ActionScheduler_Action( $random, array($random), $schedule );
			$created_actions[] = $store->save_action( $action );
		}

		$claim = $store->stake_claim(10);
		foreach ( $claim->get_actions() as $action_id ) {
			// simulate the first action interrupted by an uncatchable fatal error
			$store->log_execution( $action_id );
			break;
		}

		add_filter( 'action_scheduler_timeout_period', '__return_zero' ); // delete any finished job
		$cleaner = new ActionScheduler_QueueCleaner( $store );
		$cleaner->reset_timeouts();
		remove_filter( 'action_scheduler_timeout_period', '__return_zero' );

		$new_claim = $store->stake_claim(10);
		$this->assertCount( 4, $new_claim->get_actions() );

		add_filter( 'action_scheduler_failure_period', '__return_zero' );
		$cleaner->mark_failures();
		remove_filter( 'action_scheduler_failure_period', '__return_zero' );

		$failed = $store->query_actions(array('status' => ActionScheduler_Store::STATUS_FAILED));
		$this->assertEquals( $created_actions[0], $failed[0] );
		$this->assertCount( 1, $failed );


	}
}
 