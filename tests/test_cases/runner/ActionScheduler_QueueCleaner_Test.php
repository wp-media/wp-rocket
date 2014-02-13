<?php

/**
 * Class ActionScheduler_QueueCleaner_Test
 */
class ActionScheduler_QueueCleaner_Test extends ActionScheduler_UnitTestCase {
	public function test_delete_old_actions() {
		$store = new ActionScheduler_wpPostStore();
		$runner = new ActionScheduler_QueueRunner( $store );

		$random = md5(rand());
		$schedule = new ActionScheduler_SimpleSchedule(new DateTime('1 day ago'));

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
		$schedule = new ActionScheduler_SimpleSchedule(new DateTime('1 day ago'));

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

	public function test_reset_failed_actions() {
		$store = new ActionScheduler_wpPostStore();

		$random = md5(rand());
		$schedule = new ActionScheduler_SimpleSchedule(new DateTime('1 day ago'));

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
}
 