<?php

/**
 * Class procedural_api_Test
 */
class procedural_api_Test extends ActionScheduler_UnitTestCase {

	public function test_schedule_action() {
		$time = time();
		$hook = md5(rand());
		$action_id = wc_schedule_single_action( $time, $hook );

		$store = ActionScheduler::store();
		$action = $store->fetch_action($action_id);
		$this->assertEquals( $time, $action->get_schedule()->next()->format('U') );
		$this->assertEquals( $hook, $action->get_hook() );
	}

	public function test_recurring_action() {
		$time = time();
		$hook = md5(rand());
		$action_id = wc_schedule_recurring_action( $time, HOUR_IN_SECONDS, $hook );

		$store = ActionScheduler::store();
		$action = $store->fetch_action($action_id);
		$this->assertEquals( $time, $action->get_schedule()->next()->format('U') );
		$this->assertEquals( $time + HOUR_IN_SECONDS + 2, $action->get_schedule()->next(new DateTime('@'.($time + 2)))->format('U'));
		$this->assertEquals( $hook, $action->get_hook() );
	}

	public function test_cron_schedule() {
		$time = new DateTime('2014-01-01');
		$hook = md5(rand());
		$action_id = wc_schedule_cron_action( $time->format('U'), '0 0 10 10 *', $hook );

		$store = ActionScheduler::store();
		$action = $store->fetch_action($action_id);
		$expected_date = new DateTime('2014-10-10');
		$this->assertEquals( $expected_date->format('U'), $action->get_schedule()->next()->format('U') );
		$this->assertEquals( $hook, $action->get_hook() );
	}

	public function test_get_next() {
		$time = new DateTime('tomorrow');
		$hook = md5(rand());
		wc_schedule_recurring_action( $time->format('U'), HOUR_IN_SECONDS, $hook );

		$next = wc_next_scheduled_action( $hook );

		$this->assertEquals( $time->format('U'), $next );
	}

	public function test_unschedule() {
		$time = time();
		$hook = md5(rand());
		$action_id = wc_schedule_single_action( $time, $hook );

		wc_unschedule_action( $hook );

		$next = wc_next_scheduled_action( $hook );
		$this->assertFalse($next);

		$store = ActionScheduler::store();
		$action = $store->fetch_action($action_id);

		$this->assertNull($action->get_schedule()->next());
		$this->assertEmpty($action->get_hook());
	}
}
 