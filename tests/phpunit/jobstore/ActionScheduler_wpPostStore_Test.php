<?php

/**
 * Class ActionScheduler_wpPostStore_Test
 * @group stores
 */
class ActionScheduler_wpPostStore_Test extends ActionScheduler_UnitTestCase {

	public function test_create_action() {
		$time = new DateTime();
		$schedule = new ActionScheduler_SimpleSchedule($time);
		$action = new ActionScheduler_Action('my_hook', array(), $schedule);
		$store = new ActionScheduler_wpPostStore();
		$action_id = $store->save_action($action);

		$this->assertNotEmpty($action_id);
	}

	public function test_retrieve_action() {
		$time = new DateTime();
		$schedule = new ActionScheduler_SimpleSchedule($time);
		$action = new ActionScheduler_Action('my_hook', array(), $schedule, 'my_group');
		$store = new ActionScheduler_wpPostStore();
		$action_id = $store->save_action($action);

		$retrieved = $store->fetch_action($action_id);
		$this->assertEquals($action->get_hook(), $retrieved->get_hook());
		$this->assertEqualSets($action->get_args(), $retrieved->get_args());
		$this->assertEquals($action->get_schedule()->next()->format('U'), $retrieved->get_schedule()->next()->format('U'));
		$this->assertEquals($action->get_group(), $retrieved->get_group());
	}

	public function test_cancel_action() {
		$time = new DateTime();
		$schedule = new ActionScheduler_SimpleSchedule($time);
		$action = new ActionScheduler_Action('my_hook', array(), $schedule, 'my_group');
		$store = new ActionScheduler_wpPostStore();
		$action_id = $store->save_action($action);
		$store->cancel_action( $action_id );

		$fetched = $store->fetch_action( $action_id );
		$this->assertInstanceOf( 'ActionScheduler_NullAction', $fetched );
	}

	public function test_claim_actions() {
		$created_actions = array();
		$store = new ActionScheduler_wpPostStore();
		for ( $i = 3 ; $i > -3 ; $i-- ) {
			$time = new DateTime($i.' hours');
			$schedule = new ActionScheduler_SimpleSchedule($time);
			$action = new ActionScheduler_Action('my_hook', array($i), $schedule, 'my_group');
			$created_actions[] = $store->save_action($action);
		}

		$claim = $store->stake_claim();
		$this->assertInstanceof( 'ActionScheduler_ActionClaim', $claim );

		$this->assertCount( 3, $claim->get_actions() );
		$this->assertEqualSets( array_slice( $created_actions, 3, 3 ), $claim->get_actions() );
	}

	public function test_duplicate_claim() {
		$created_actions = array();
		$store = new ActionScheduler_wpPostStore();
		for ( $i = 0 ; $i > -3 ; $i-- ) {
			$time = new DateTime($i.' hours');
			$schedule = new ActionScheduler_SimpleSchedule($time);
			$action = new ActionScheduler_Action('my_hook', array($i), $schedule, 'my_group');
			$created_actions[] = $store->save_action($action);
		}

		$claim1 = $store->stake_claim();
		$claim2 = $store->stake_claim();
		$this->assertCount( 3, $claim1->get_actions() );
		$this->assertCount( 0, $claim2->get_actions() );
	}

	public function test_release_claim() {
		$created_actions = array();
		$store = new ActionScheduler_wpPostStore();
		for ( $i = 0 ; $i > -3 ; $i-- ) {
			$time = new DateTime($i.' hours');
			$schedule = new ActionScheduler_SimpleSchedule($time);
			$action = new ActionScheduler_Action('my_hook', array($i), $schedule, 'my_group');
			$created_actions[] = $store->save_action($action);
		}

		$claim1 = $store->stake_claim();

		$store->release_claim( $claim1 );

		$claim2 = $store->stake_claim();
		$this->assertCount( 3, $claim2->get_actions() );
	}

	public function test_search() {
		$created_actions = array();
		$store = new ActionScheduler_wpPostStore();
		for ( $i = -3 ; $i <= 3 ; $i++ ) {
			$time = new DateTime($i.' hours');
			$schedule = new ActionScheduler_SimpleSchedule($time);
			$action = new ActionScheduler_Action('my_hook', array($i), $schedule, 'my_group');
			$created_actions[] = $store->save_action($action);
		}

		$next_no_args = $store->find_action( 'my_hook' );
		$this->assertEquals( $created_actions[0], $next_no_args );

		$next_with_args = $store->find_action( 'my_hook', array( 'args' => array( 1 ) ) );
		$this->assertEquals( $created_actions[4], $next_with_args );

		$non_existent = $store->find_action( 'my_hook', array( 'args' => array( 17 ) ) );
		$this->assertNull( $non_existent );
	}

	public function test_search_by_group() {
		$store = new ActionScheduler_wpPostStore();
		$schedule = new ActionScheduler_SimpleSchedule(new DateTime('tomorrow'));
		$abc = $store->save_action(new ActionScheduler_Action('my_hook', array(1), $schedule, 'abc'));
		$def = $store->save_action(new ActionScheduler_Action('my_hook', array(1), $schedule, 'def'));
		$ghi = $store->save_action(new ActionScheduler_Action('my_hook', array(1), $schedule, 'ghi'));

		$this->assertEquals( $abc, $store->find_action('my_hook', array('group' => 'abc')));
		$this->assertEquals( $def, $store->find_action('my_hook', array('group' => 'def')));
		$this->assertEquals( $ghi, $store->find_action('my_hook', array('group' => 'ghi')));
	}
}
 