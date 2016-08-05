<?php

/**
 * Class ActionScheduler_wpPostStore_Test
 * @group stores
 */
class ActionScheduler_wpPostStore_Test extends ActionScheduler_UnitTestCase {

	public function test_create_action() {
		$time = as_get_datetime_object();
		$schedule = new ActionScheduler_SimpleSchedule($time);
		$action = new ActionScheduler_Action('my_hook', array(), $schedule);
		$store = new ActionScheduler_wpPostStore();
		$action_id = $store->save_action($action);

		$this->assertNotEmpty($action_id);
	}

	public function test_retrieve_action() {
		$time = as_get_datetime_object();
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
		$time = as_get_datetime_object();
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
			$time = as_get_datetime_object($i.' hours');
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
			$time = as_get_datetime_object($i.' hours');
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
			$time = as_get_datetime_object($i.' hours');
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
			$time = as_get_datetime_object($i.' hours');
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
		$schedule = new ActionScheduler_SimpleSchedule(as_get_datetime_object('tomorrow'));
		$abc = $store->save_action(new ActionScheduler_Action('my_hook', array(1), $schedule, 'abc'));
		$def = $store->save_action(new ActionScheduler_Action('my_hook', array(1), $schedule, 'def'));
		$ghi = $store->save_action(new ActionScheduler_Action('my_hook', array(1), $schedule, 'ghi'));

		$this->assertEquals( $abc, $store->find_action('my_hook', array('group' => 'abc')));
		$this->assertEquals( $def, $store->find_action('my_hook', array('group' => 'def')));
		$this->assertEquals( $ghi, $store->find_action('my_hook', array('group' => 'ghi')));
	}

	public function test_post_author() {
		$current_user = get_current_user_id();

		$time = as_get_datetime_object();
		$schedule = new ActionScheduler_SimpleSchedule($time);
		$action = new ActionScheduler_Action('my_hook', array(), $schedule);
		$store = new ActionScheduler_wpPostStore();
		$action_id = $store->save_action($action);

		$post = get_post($action_id);
		$this->assertEquals(0, $post->post_author);

		$new_user = $this->factory->user->create_object(array(
			'user_login' => __FUNCTION__,
			'user_pass' => md5(rand()),
		));
		wp_set_current_user( $new_user );


		$schedule = new ActionScheduler_SimpleSchedule($time);
		$action = new ActionScheduler_Action('my_hook', array(), $schedule);
		$action_id = $store->save_action($action);
		$post = get_post($action_id);
		$this->assertEquals(0, $post->post_author);

		wp_set_current_user($current_user);
	}

	/**
	 * @issue 13
	 */
	public function test_post_status_for_recurring_action() {
		$time = as_get_datetime_object('10 minutes');
		$schedule = new ActionScheduler_IntervalSchedule($time, HOUR_IN_SECONDS);
		$action = new ActionScheduler_Action('my_hook', array(), $schedule);
		$store = new ActionScheduler_wpPostStore();
		$action_id = $store->save_action($action);

		$action = $store->fetch_action($action_id);
		$action->execute();
		$store->mark_complete( $action_id );

		$next = $action->get_schedule()->next( as_get_datetime_object() );
		$new_action_id = $store->save_action( $action, $next );

		$this->assertEquals('publish', get_post_status($action_id));
		$this->assertEquals('pending', get_post_status($new_action_id));
	}

	public function test_get_run_date() {
		$time = as_get_datetime_object('-10 minutes');
		$schedule = new ActionScheduler_IntervalSchedule($time, HOUR_IN_SECONDS);
		$action = new ActionScheduler_Action('my_hook', array(), $schedule);
		$store = new ActionScheduler_wpPostStore();
		$action_id = $store->save_action($action);

		$this->assertEquals( $store->get_date($action_id)->format('U'), $time->format('U') );

		$action = $store->fetch_action($action_id);
		$action->execute();
		$now = as_get_datetime_object();
		$store->mark_complete( $action_id );

		$this->assertEquals( $store->get_date($action_id)->format('U'), $now->format('U') );

		$next = $action->get_schedule()->next( $now );
		$new_action_id = $store->save_action( $action, $next );

		$this->assertEquals( (int)($now->format('U')) + HOUR_IN_SECONDS, $store->get_date($new_action_id)->format('U') );
	}
}
 