<?php

namespace Action_Scheduler\Tests\DataStores;

use ActionScheduler_Action;
use ActionScheduler_IntervalSchedule;
use ActionScheduler_SimpleSchedule;
use ActionScheduler_Store;
use ActionScheduler_UnitTestCase;
use InvalidArgumentException;

/**
 * Abstract store test class.
 *
 * Many tests for the WP Post store or the custom tables store can be shared. This abstract class contains tests that
 * apply to both stores without having to duplicate code.
 */
abstract class AbstractStoreTest extends ActionScheduler_UnitTestCase {

	/**
	 * Get data store for tests.
	 *
	 * @return ActionScheduler_Store
	 */
	abstract protected function get_store();

	public function test_get_status() {
		$time = as_get_datetime_object('-10 minutes');
		$schedule = new ActionScheduler_IntervalSchedule($time, HOUR_IN_SECONDS);
		$action = new ActionScheduler_Action('my_hook', array(), $schedule);
		$store = $this->get_store();
		$action_id = $store->save_action($action);

		$this->assertEquals( ActionScheduler_Store::STATUS_PENDING, $store->get_status( $action_id ) );

		$store->mark_complete( $action_id );
		$this->assertEquals( ActionScheduler_Store::STATUS_COMPLETE, $store->get_status( $action_id ) );

		$store->mark_failure( $action_id );
		$this->assertEquals( ActionScheduler_Store::STATUS_FAILED, $store->get_status( $action_id ) );
	}

	/* Start tests for \ActionScheduler_Store::query_actions() */

	public function test_query_actions_query_type_arg_invalid_option() {
		$this->expectException( InvalidArgumentException::class );
		$this->get_store()->query_actions( array( 'hook' => 'my_hook' ), 'invalid' );
	}

	public function test_query_actions_query_type_arg_valid_options() {
		$store = $this->get_store();
		$schedule = new ActionScheduler_SimpleSchedule( as_get_datetime_object( 'tomorrow' ) );

		$action_id_1 = $store->save_action( new ActionScheduler_Action( 'my_hook', array( 1 ), $schedule ) );
		$action_id_2 = $store->save_action( new ActionScheduler_Action( 'my_hook', array( 1 ), $schedule ) );

		$this->assertEquals( array( $action_id_1, $action_id_2 ), $store->query_actions( array( 'hook' => 'my_hook' ) ) );
		$this->assertEquals( 2, $store->query_actions( array( 'hook' => 'my_hook' ), 'count' ) );
	}

	public function test_query_actions_by_single_status() {
		$store = $this->get_store();
		$schedule = new ActionScheduler_SimpleSchedule( as_get_datetime_object( 'tomorrow' ) );

		$this->assertEquals( 0, $store->query_actions( array( 'status' => ActionScheduler_Store::STATUS_PENDING ), 'count' ) );

		$action_id_1 = $store->save_action( new ActionScheduler_Action( 'my_hook_1', array( 1 ), $schedule ) );
		$action_id_2 = $store->save_action( new ActionScheduler_Action( 'my_hook_2', array( 1 ), $schedule ) );
		$action_id_3 = $store->save_action( new ActionScheduler_Action( 'my_hook_3', array( 1 ), $schedule ) );
		$store->mark_complete( $action_id_3 );

		$this->assertEquals( 2, $store->query_actions( array( 'status' => ActionScheduler_Store::STATUS_PENDING ), 'count' ) );
		$this->assertEquals( 1, $store->query_actions( array( 'status' => ActionScheduler_Store::STATUS_COMPLETE ), 'count' ) );
	}

	public function test_query_actions_by_array_status() {
		$store = $this->get_store();
		$schedule = new ActionScheduler_SimpleSchedule( as_get_datetime_object( 'tomorrow' ) );

		$this->assertEquals(
			0,
			$store->query_actions(
				array(
					'status' => array( ActionScheduler_Store::STATUS_PENDING, ActionScheduler_Store::STATUS_RUNNING ),
				),
				'count'
			)
		);

		$action_id_1 = $store->save_action( new ActionScheduler_Action( 'my_hook_1', array( 1 ), $schedule ) );
		$action_id_2 = $store->save_action( new ActionScheduler_Action( 'my_hook_2', array( 1 ), $schedule ) );
		$action_id_3 = $store->save_action( new ActionScheduler_Action( 'my_hook_3', array( 1 ), $schedule ) );
		$store->mark_failure( $action_id_3 );

		$this->assertEquals(
			3,
			$store->query_actions(
				array(
					'status' => array( ActionScheduler_Store::STATUS_PENDING, ActionScheduler_Store::STATUS_FAILED ),
				),
				'count'
			)
		);
		$this->assertEquals(
			2,
			$store->query_actions(
				array(
					'status' => array( ActionScheduler_Store::STATUS_PENDING, ActionScheduler_Store::STATUS_COMPLETE ),
				),
				'count'
			)
		);
	}

	/* End tests for \ActionScheduler_Store::query_actions() */

}
