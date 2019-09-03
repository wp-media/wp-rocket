<?php

/**
 * Class ActionScheduler_DBStore_Test
 * @group tables
 */
class ActionScheduler_DBStore_Test extends ActionScheduler_UnitTestCase {

	public function test_create_action() {
		$time      = as_get_datetime_object();
		$schedule  = new ActionScheduler_SimpleSchedule( $time );
		$action    = new ActionScheduler_Action( 'my_hook', [], $schedule );
		$store     = new ActionScheduler_DBStore();
		$action_id = $store->save_action( $action );

		$this->assertNotEmpty( $action_id );
	}

	public function test_create_action_with_scheduled_date() {
		$time        = as_get_datetime_object( strtotime( '-1 week' ) );
		$action      = new ActionScheduler_Action( 'my_hook', [], new ActionScheduler_SimpleSchedule( $time ) );
		$store       = new ActionScheduler_DBStore();
		$action_id   = $store->save_action( $action, $time );
		$action_date = $store->get_date( $action_id );

		$this->assertEquals( $time->format( 'U' ), $action_date->format( 'U' ) );
	}

	public function test_retrieve_action() {
		$time      = as_get_datetime_object();
		$schedule  = new ActionScheduler_SimpleSchedule( $time );
		$action    = new ActionScheduler_Action( 'my_hook', [], $schedule, 'my_group' );
		$store     = new ActionScheduler_DBStore();
		$action_id = $store->save_action( $action );

		$retrieved = $store->fetch_action( $action_id );
		$this->assertEquals( $action->get_hook(), $retrieved->get_hook() );
		$this->assertEqualSets( $action->get_args(), $retrieved->get_args() );
		$this->assertEquals( $action->get_schedule()->get_date()->format( 'U' ), $retrieved->get_schedule()->get_date()->format( 'U' ) );
		$this->assertEquals( $action->get_group(), $retrieved->get_group() );
	}

	public function test_cancel_action() {
		$time      = as_get_datetime_object();
		$schedule  = new ActionScheduler_SimpleSchedule( $time );
		$action    = new ActionScheduler_Action( 'my_hook', [], $schedule, 'my_group' );
		$store     = new ActionScheduler_DBStore();
		$action_id = $store->save_action( $action );
		$store->cancel_action( $action_id );

		$fetched = $store->fetch_action( $action_id );
		$this->assertInstanceOf( 'ActionScheduler_CanceledAction', $fetched );
	}

	public function test_cancel_actions_by_hook() {
		$store   = new ActionScheduler_DBStore();
		$actions = [];
		$hook    = 'by_hook_test';
		for ( $day = 1; $day <= 3; $day++ ) {
			$delta     = sprintf( '+%d day', $day );
			$time      = as_get_datetime_object( $delta );
			$schedule  = new ActionScheduler_SimpleSchedule( $time );
			$action    = new ActionScheduler_Action( $hook, [], $schedule, 'my_group' );
			$actions[] = $store->save_action( $action );
		}
		$store->cancel_actions_by_hook( $hook );

		foreach ( $actions as $action_id ) {
			$fetched = $store->fetch_action( $action_id );
			$this->assertInstanceOf( 'ActionScheduler_CanceledAction', $fetched );
		}
	}

	public function test_cancel_actions_by_group() {
		$store   = new ActionScheduler_DBStore();
		$actions = [];
		$group   = 'by_group_test';
		for ( $day = 1; $day <= 3; $day++ ) {
			$delta     = sprintf( '+%d day', $day );
			$time      = as_get_datetime_object( $delta );
			$schedule  = new ActionScheduler_SimpleSchedule( $time );
			$action    = new ActionScheduler_Action( 'my_hook', [], $schedule, $group );
			$actions[] = $store->save_action( $action );
		}
		$store->cancel_actions_by_group( $group );

		foreach ( $actions as $action_id ) {
			$fetched = $store->fetch_action( $action_id );
			$this->assertInstanceOf( 'ActionScheduler_CanceledAction', $fetched );
		}
	}

	public function test_claim_actions() {
		$created_actions = [];
		$store           = new ActionScheduler_DBStore();
		for ( $i = 3; $i > - 3; $i -- ) {
			$time     = as_get_datetime_object( $i . ' hours' );
			$schedule = new ActionScheduler_SimpleSchedule( $time );
			$action   = new ActionScheduler_Action( 'my_hook', [ $i ], $schedule, 'my_group' );

			$created_actions[] = $store->save_action( $action );
		}

		$claim = $store->stake_claim();
		$this->assertInstanceof( 'ActionScheduler_ActionClaim', $claim );

		$this->assertCount( 3, $claim->get_actions() );
		$this->assertEqualSets( array_slice( $created_actions, 3, 3 ), $claim->get_actions() );
	}

	public function test_claim_actions_order() {

		$store           = new ActionScheduler_DBStore();
		$schedule        = new ActionScheduler_SimpleSchedule( as_get_datetime_object( '-1 hour' ) );
		$created_actions = array(
			$store->save_action( new ActionScheduler_Action( 'my_hook', array( 1 ), $schedule, 'my_group' ) ),
			$store->save_action( new ActionScheduler_Action( 'my_hook', array( 1 ), $schedule, 'my_group' ) ),
		);

		$claim = $store->stake_claim();
		$this->assertInstanceof( 'ActionScheduler_ActionClaim', $claim );

		// Verify uniqueness of action IDs.
		$this->assertEquals( 2, count( array_unique( $created_actions ) ) );

		// Verify the count and order of the actions.
		$claimed_actions = $claim->get_actions();
		$this->assertCount( 2, $claimed_actions );
		$this->assertEquals( $created_actions, $claimed_actions );

		// Verify the reversed order doesn't pass.
		$reversed_actions = array_reverse( $created_actions );
		$this->assertNotEquals( $reversed_actions, $claimed_actions );
	}

	public function test_claim_actions_by_hooks() {
		$created_actions = $created_actions_by_hook = [];
		$store           = new ActionScheduler_DBStore();
		$unique_hook_one = 'my_unique_hook_one';
		$unique_hook_two = 'my_unique_hook_two';
		$unique_hooks    = array(
			$unique_hook_one,
			$unique_hook_two,
		);

		for ( $i = 3; $i > - 3; $i -- ) {
			foreach ( $unique_hooks as $unique_hook ) {
				$time     = as_get_datetime_object( $i . ' hours' );
				$schedule = new ActionScheduler_SimpleSchedule( $time );
				$action   = new ActionScheduler_Action( $unique_hook, [ $i ], $schedule, 'my_group' );

				$action_id         = $store->save_action( $action );
				$created_actions[] = $created_actions_by_hook[ $unique_hook ][] = $action_id;
			}
		}

		$claim = $store->stake_claim( 10, null, $unique_hooks );
		$this->assertInstanceof( 'ActionScheduler_ActionClaim', $claim );
		$this->assertCount( 6, $claim->get_actions() );
		$this->assertEqualSets( array_slice( $created_actions, 6, 6 ), $claim->get_actions() );

		$store->release_claim( $claim );

		$claim = $store->stake_claim( 10, null, array( $unique_hook_one ) );
		$this->assertInstanceof( 'ActionScheduler_ActionClaim', $claim );
		$this->assertCount( 3, $claim->get_actions() );
		$this->assertEqualSets( array_slice( $created_actions_by_hook[ $unique_hook_one ], 3, 3 ), $claim->get_actions() );

		$store->release_claim( $claim );

		$claim = $store->stake_claim( 10, null, array( $unique_hook_two ) );
		$this->assertInstanceof( 'ActionScheduler_ActionClaim', $claim );
		$this->assertCount( 3, $claim->get_actions() );
		$this->assertEqualSets( array_slice( $created_actions_by_hook[ $unique_hook_two ], 3, 3 ), $claim->get_actions() );
	}

	public function test_claim_actions_by_group() {
		$created_actions  = [];
		$store            = new ActionScheduler_DBStore();
		$unique_group_one = 'my_unique_group_one';
		$unique_group_two = 'my_unique_group_two';
		$unique_groups    = array(
			$unique_group_one,
			$unique_group_two,
		);

		for ( $i = 3; $i > - 3; $i -- ) {
			foreach ( $unique_groups as $unique_group ) {
				$time     = as_get_datetime_object( $i . ' hours' );
				$schedule = new ActionScheduler_SimpleSchedule( $time );
				$action   = new ActionScheduler_Action( 'my_hook', [ $i ], $schedule, $unique_group );

				$created_actions[ $unique_group ][] = $store->save_action( $action );
			}
		}

		$claim = $store->stake_claim( 10, null, array(), $unique_group_one );
		$this->assertInstanceof( 'ActionScheduler_ActionClaim', $claim );
		$this->assertCount( 3, $claim->get_actions() );
		$this->assertEqualSets( array_slice( $created_actions[ $unique_group_one ], 3, 3 ), $claim->get_actions() );

		$store->release_claim( $claim );

		$claim = $store->stake_claim( 10, null, array(), $unique_group_two );
		$this->assertInstanceof( 'ActionScheduler_ActionClaim', $claim );
		$this->assertCount( 3, $claim->get_actions() );
		$this->assertEqualSets( array_slice( $created_actions[ $unique_group_two ], 3, 3 ), $claim->get_actions() );
	}

	public function test_claim_actions_by_hook_and_group() {
		$created_actions = $created_actions_by_hook = [];
		$store           = new ActionScheduler_DBStore();

		$unique_hook_one = 'my_other_unique_hook_one';
		$unique_hook_two = 'my_other_unique_hook_two';
		$unique_hooks    = array(
			$unique_hook_one,
			$unique_hook_two,
		);

		$unique_group_one = 'my_other_other_unique_group_one';
		$unique_group_two = 'my_other_unique_group_two';
		$unique_groups    = array(
			$unique_group_one,
			$unique_group_two,
		);

		for ( $i = 3; $i > - 3; $i -- ) {
			foreach ( $unique_hooks as $unique_hook ) {
				foreach ( $unique_groups as $unique_group ) {
					$time     = as_get_datetime_object( $i . ' hours' );
					$schedule = new ActionScheduler_SimpleSchedule( $time );
					$action   = new ActionScheduler_Action( $unique_hook, [ $i ], $schedule, $unique_group );

					$action_id = $store->save_action( $action );
					$created_actions[ $unique_group ][] = $action_id;
					$created_actions_by_hook[ $unique_hook ][ $unique_group ][] = $action_id;
				}
			}
		}

		/** Test Both Hooks with Each Group */

		$claim = $store->stake_claim( 10, null, $unique_hooks, $unique_group_one );
		$this->assertInstanceof( 'ActionScheduler_ActionClaim', $claim );
		$this->assertCount( 6, $claim->get_actions() );
		$this->assertEqualSets( array_slice( $created_actions[ $unique_group_one ], 6, 6 ), $claim->get_actions() );

		$store->release_claim( $claim );

		$claim = $store->stake_claim( 10, null, $unique_hooks, $unique_group_two );
		$this->assertInstanceof( 'ActionScheduler_ActionClaim', $claim );
		$this->assertCount( 6, $claim->get_actions() );
		$this->assertEqualSets( array_slice( $created_actions[ $unique_group_two ], 6, 6 ), $claim->get_actions() );

		$store->release_claim( $claim );

		/** Test Just One Hook with Group One */

		$claim = $store->stake_claim( 10, null, array( $unique_hook_one ), $unique_group_one );
		$this->assertInstanceof( 'ActionScheduler_ActionClaim', $claim );
		$this->assertCount( 3, $claim->get_actions() );
		$this->assertEqualSets( array_slice( $created_actions_by_hook[ $unique_hook_one ][ $unique_group_one ], 3, 3 ), $claim->get_actions() );

		$store->release_claim( $claim );

		$claim = $store->stake_claim( 24, null, array( $unique_hook_two ), $unique_group_one );
		$this->assertInstanceof( 'ActionScheduler_ActionClaim', $claim );
		$this->assertCount( 3, $claim->get_actions() );
		$this->assertEqualSets( array_slice( $created_actions_by_hook[ $unique_hook_two ][ $unique_group_one ], 3, 3 ), $claim->get_actions() );

		$store->release_claim( $claim );

		/** Test Just One Hook with Group Two */

		$claim = $store->stake_claim( 10, null, array( $unique_hook_one ), $unique_group_two );
		$this->assertInstanceof( 'ActionScheduler_ActionClaim', $claim );
		$this->assertCount( 3, $claim->get_actions() );
		$this->assertEqualSets( array_slice( $created_actions_by_hook[ $unique_hook_one ][ $unique_group_two ], 3, 3 ), $claim->get_actions() );

		$store->release_claim( $claim );

		$claim = $store->stake_claim( 24, null, array( $unique_hook_two ), $unique_group_two );
		$this->assertInstanceof( 'ActionScheduler_ActionClaim', $claim );
		$this->assertCount( 3, $claim->get_actions() );
		$this->assertEqualSets( array_slice( $created_actions_by_hook[ $unique_hook_two ][ $unique_group_two ], 3, 3 ), $claim->get_actions() );
	}

	public function test_duplicate_claim() {
		$created_actions = [];
		$store           = new ActionScheduler_DBStore();
		for ( $i = 0; $i > - 3; $i -- ) {
			$time     = as_get_datetime_object( $i . ' hours' );
			$schedule = new ActionScheduler_SimpleSchedule( $time );
			$action   = new ActionScheduler_Action( 'my_hook', [ $i ], $schedule, 'my_group' );

			$created_actions[] = $store->save_action( $action );
		}

		$claim1 = $store->stake_claim();
		$claim2 = $store->stake_claim();
		$this->assertCount( 3, $claim1->get_actions() );
		$this->assertCount( 0, $claim2->get_actions() );
	}

	public function test_release_claim() {
		$created_actions = [];
		$store           = new ActionScheduler_DBStore();
		for ( $i = 0; $i > - 3; $i -- ) {
			$time     = as_get_datetime_object( $i . ' hours' );
			$schedule = new ActionScheduler_SimpleSchedule( $time );
			$action   = new ActionScheduler_Action( 'my_hook', [ $i ], $schedule, 'my_group' );

			$created_actions[] = $store->save_action( $action );
		}

		$claim1 = $store->stake_claim();

		$store->release_claim( $claim1 );

		$claim2 = $store->stake_claim();
		$this->assertCount( 3, $claim2->get_actions() );
	}

	public function test_search() {
		$created_actions = [];
		$store           = new ActionScheduler_DBStore();
		for ( $i = - 3; $i <= 3; $i ++ ) {
			$time     = as_get_datetime_object( $i . ' hours' );
			$schedule = new ActionScheduler_SimpleSchedule( $time );
			$action   = new ActionScheduler_Action( 'my_hook', [ $i ], $schedule, 'my_group' );

			$created_actions[] = $store->save_action( $action );
		}

		$next_no_args = $store->find_action( 'my_hook' );
		$this->assertEquals( $created_actions[ 0 ], $next_no_args );

		$next_with_args = $store->find_action( 'my_hook', [ 'args' => [ 1 ] ] );
		$this->assertEquals( $created_actions[ 4 ], $next_with_args );

		$non_existent = $store->find_action( 'my_hook', [ 'args' => [ 17 ] ] );
		$this->assertNull( $non_existent );
	}

	public function test_search_by_group() {
		$store    = new ActionScheduler_DBStore();
		$schedule = new ActionScheduler_SimpleSchedule( as_get_datetime_object( 'tomorrow' ) );

		$abc = $store->save_action( new ActionScheduler_Action( 'my_hook', [ 1 ], $schedule, 'abc' ) );
		$def = $store->save_action( new ActionScheduler_Action( 'my_hook', [ 1 ], $schedule, 'def' ) );
		$ghi = $store->save_action( new ActionScheduler_Action( 'my_hook', [ 1 ], $schedule, 'ghi' ) );

		$this->assertEquals( $abc, $store->find_action( 'my_hook', [ 'group' => 'abc' ] ) );
		$this->assertEquals( $def, $store->find_action( 'my_hook', [ 'group' => 'def' ] ) );
		$this->assertEquals( $ghi, $store->find_action( 'my_hook', [ 'group' => 'ghi' ] ) );
	}

	public function test_get_run_date() {
		$time      = as_get_datetime_object( '-10 minutes' );
		$schedule  = new ActionScheduler_IntervalSchedule( $time, HOUR_IN_SECONDS );
		$action    = new ActionScheduler_Action( 'my_hook', [], $schedule );
		$store     = new ActionScheduler_DBStore();
		$action_id = $store->save_action( $action );

		$this->assertEquals( $time->format( 'U' ), $store->get_date( $action_id )->format( 'U' ) );

		$action = $store->fetch_action( $action_id );
		$action->execute();
		$now = as_get_datetime_object();
		$store->mark_complete( $action_id );

		$this->assertEquals( $now->format( 'U' ), $store->get_date( $action_id )->format( 'U' ) );

		$next          = $action->get_schedule()->get_next( $now );
		$new_action_id = $store->save_action( $action, $next );

		$this->assertEquals( (int) ( $now->format( 'U' ) ) + HOUR_IN_SECONDS, $store->get_date( $new_action_id )->format( 'U' ) );
	}

	public function test_get_status() {
		$time = as_get_datetime_object('-10 minutes');
		$schedule = new ActionScheduler_IntervalSchedule($time, HOUR_IN_SECONDS);
		$action = new ActionScheduler_Action('my_hook', array(), $schedule);
		$store = new ActionScheduler_DBStore();
		$action_id = $store->save_action($action);

		$this->assertEquals( ActionScheduler_Store::STATUS_PENDING, $store->get_status( $action_id ) );

		$store->mark_complete( $action_id );
		$this->assertEquals( ActionScheduler_Store::STATUS_COMPLETE, $store->get_status( $action_id ) );

		$store->mark_failure( $action_id );
		$this->assertEquals( ActionScheduler_Store::STATUS_FAILED, $store->get_status( $action_id ) );
	}
}
