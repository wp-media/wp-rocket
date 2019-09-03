<?php

/**
 * Class ActionScheduler_DBStoreMigrator_Test
 * @group tables
 */
class ActionScheduler_DBStoreMigrator_Test extends ActionScheduler_UnitTestCase {

	public function test_create_action_with_last_attempt_date() {
		$scheduled_date    = as_get_datetime_object( strtotime( '-24 hours' ) );
		$last_attempt_date = as_get_datetime_object( strtotime( '-23 hours' ) );

		$action = new ActionScheduler_FinishedAction( 'my_hook', [], new ActionScheduler_SimpleSchedule( $scheduled_date ) );
		$store  = new ActionScheduler_DBStoreMigrator();

		$action_id   = $store->save_action( $action, null, $last_attempt_date );
		$action_date = $store->get_date( $action_id );

		$this->assertEquals( $last_attempt_date->format( 'U' ), $action_date->format( 'U' ) );

		$action_id   = $store->save_action( $action, $scheduled_date, $last_attempt_date );
		$action_date = $store->get_date( $action_id );

		$this->assertEquals( $last_attempt_date->format( 'U' ), $action_date->format( 'U' ) );
	}
}
