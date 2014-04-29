<?php

/**
 * Class ActionScheduler_QueueRunner_Test
 * @group runners
 */
class ActionScheduler_QueueRunner_Test extends ActionScheduler_UnitTestCase {
	public function test_create_runner() {
		$store = new ActionScheduler_wpPostStore();
		$runner = new ActionScheduler_QueueRunner( $store );
		$actions_run = $runner->run();

		$this->assertEquals( 0, $actions_run );
	}

	public function test_run() {
		$store = new ActionScheduler_wpPostStore();
		$runner = new ActionScheduler_QueueRunner( $store );

		$mock = new MockAction();
		$random = md5(rand());
		add_action( $random, array( $mock, 'action' ) );
		$schedule = new ActionScheduler_SimpleSchedule(new DateTime('1 day ago'));

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
		$store = new ActionScheduler_wpPostStore();
		$runner = new ActionScheduler_QueueRunner( $store );

		$mock = new MockAction();
		$random = md5(rand());
		add_action( $random, array( $mock, 'action' ) );
		$schedule = new ActionScheduler_SimpleSchedule(new DateTime('1 day ago'));

		for ( $i = 0 ; $i < 3 ; $i++ ) {
			$action = new ActionScheduler_Action( $random, array($random), $schedule );
			$store->save_action( $action );
		}

		$schedule = new ActionScheduler_SimpleSchedule(new DateTime('tomorrow'));
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
		$store = new ActionScheduler_wpPostStore();
		$runner = new ActionScheduler_QueueRunner( $store );

		$random = md5(rand());
		$schedule = new ActionScheduler_SimpleSchedule(new DateTime('12 hours ago'));

		$action = new ActionScheduler_Action( $random, array(), $schedule );
		$action_id = $store->save_action( $action );

		$runner->run();

		$finished_action = $store->fetch_action( $action_id );

		$this->assertTrue( $finished_action->is_finished() );
	}

	public function test_next_instance_of_action() {
		$store = new ActionScheduler_wpPostStore();
		$runner = new ActionScheduler_QueueRunner( $store );

		$random = md5(rand());
		$schedule = new ActionScheduler_IntervalSchedule(new DateTime('12 hours ago'), DAY_IN_SECONDS);

		$action = new ActionScheduler_Action( $random, array(), $schedule );
		$store->save_action( $action );

		$runner->run();

		$claim = $store->stake_claim(10, new DateTime((DAY_IN_SECONDS - 60).' seconds'));
		$this->assertCount(0, $claim->get_actions());

		$claim = $store->stake_claim(10, new DateTime(DAY_IN_SECONDS.' seconds'));
		$actions = $claim->get_actions();
		$this->assertCount(1, $actions);

		$action_id = reset($actions);
		$new_action = $store->fetch_action($action_id);


		$this->assertEquals( $random, $new_action->get_hook() );
		$this->assertEquals( $schedule->next(new DateTime()), $new_action->get_schedule()->next(new DateTime()) );
	}

	public function test_hooked_into_wp_cron() {
		$next = wp_next_scheduled( ActionScheduler_QueueRunner::WP_CRON_HOOK );
		$this->assertNotEmpty($next);
	}
}
 