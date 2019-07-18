<?php

use Action_Scheduler\Migration\ActionMigrator;
use Action_Scheduler\Migration\LogMigrator;

/**
 * Class ActionMigrator_Test
 * @group migration
 */
class ActionMigrator_Test extends ActionScheduler_UnitTestCase {
	public function setUp() {
		parent::setUp();
		if ( ! taxonomy_exists( ActionScheduler_wpPostStore::GROUP_TAXONOMY )  ) {
			// register the post type and taxonomy necessary for the store to work
			$store = new ActionScheduler_wpPostStore();
			$store->init();
		}
	}

	public function test_migrate_from_wpPost_to_db() {
		$source = new ActionScheduler_wpPostStore();
		$destination = new ActionScheduler_DBStore();
		$migrator = new ActionMigrator( $source, $destination, $this->get_log_migrator() );

		$time      = as_get_datetime_object();
		$schedule  = new ActionScheduler_SimpleSchedule( $time );
		$action    = new ActionScheduler_Action( 'my_hook', [], $schedule, 'my_group' );
		$action_id = $source->save_action( $action );

		$new_id = $migrator->migrate( $action_id );

		// ensure we get the same record out of the new store as we stored in the old
		$retrieved = $destination->fetch_action( $new_id );
		$this->assertEquals( $action->get_hook(), $retrieved->get_hook() );
		$this->assertEqualSets( $action->get_args(), $retrieved->get_args() );
		$this->assertEquals( $action->get_schedule()->get_date()->format( 'U' ), $retrieved->get_schedule()->get_date()->format( 'U' ) );
		$this->assertEquals( $action->get_group(), $retrieved->get_group() );
		$this->assertEquals( \ActionScheduler_Store::STATUS_PENDING, $destination->get_status( $new_id ) );


		// ensure that the record in the old store does not exist
		$old_action = $source->fetch_action( $action_id );
		$this->assertInstanceOf( 'ActionScheduler_NullAction', $old_action );
	}

	public function test_does_not_migrate_missing_action_from_wpPost_to_db() {
		$source = new ActionScheduler_wpPostStore();
		$destination = new ActionScheduler_DBStore();
		$migrator = new ActionMigrator( $source, $destination, $this->get_log_migrator() );

		$action_id = rand( 100, 100000 );

		$new_id = $migrator->migrate( $action_id );
		$this->assertEquals( 0, $new_id );

		// ensure we get the same record out of the new store as we stored in the old
		$retrieved = $destination->fetch_action( $new_id );
		$this->assertInstanceOf( 'ActionScheduler_NullAction', $retrieved );
	}

	public function test_migrate_completed_action_from_wpPost_to_db() {
		$source = new ActionScheduler_wpPostStore();
		$destination = new ActionScheduler_DBStore();
		$migrator = new ActionMigrator( $source, $destination, $this->get_log_migrator() );

		$time      = as_get_datetime_object();
		$schedule  = new ActionScheduler_SimpleSchedule( $time );
		$action    = new ActionScheduler_Action( 'my_hook', [], $schedule, 'my_group' );
		$action_id = $source->save_action( $action );
		$source->mark_complete( $action_id );

		$new_id = $migrator->migrate( $action_id );

		// ensure we get the same record out of the new store as we stored in the old
		$retrieved = $destination->fetch_action( $new_id );
		$this->assertEquals( $action->get_hook(), $retrieved->get_hook() );
		$this->assertEqualSets( $action->get_args(), $retrieved->get_args() );
		$this->assertEquals( $action->get_schedule()->get_date()->format( 'U' ), $retrieved->get_schedule()->get_date()->format( 'U' ) );
		$this->assertEquals( $action->get_group(), $retrieved->get_group() );
		$this->assertTrue( $retrieved->is_finished() );
		$this->assertEquals( \ActionScheduler_Store::STATUS_COMPLETE, $destination->get_status( $new_id ) );

		// ensure that the record in the old store does not exist
		$old_action = $source->fetch_action( $action_id );
		$this->assertInstanceOf( 'ActionScheduler_NullAction', $old_action );
	}

	public function test_migrate_failed_action_from_wpPost_to_db() {
		$source = new ActionScheduler_wpPostStore();
		$destination = new ActionScheduler_DBStore();
		$migrator = new ActionMigrator( $source, $destination, $this->get_log_migrator() );

		$time      = as_get_datetime_object();
		$schedule  = new ActionScheduler_SimpleSchedule( $time );
		$action    = new ActionScheduler_Action( 'my_hook', [], $schedule, 'my_group' );
		$action_id = $source->save_action( $action );
		$source->mark_failure( $action_id );

		$new_id = $migrator->migrate( $action_id );

		// ensure we get the same record out of the new store as we stored in the old
		$retrieved = $destination->fetch_action( $new_id );
		$this->assertEquals( $action->get_hook(), $retrieved->get_hook() );
		$this->assertEqualSets( $action->get_args(), $retrieved->get_args() );
		$this->assertEquals( $action->get_schedule()->get_date()->format( 'U' ), $retrieved->get_schedule()->get_date()->format( 'U' ) );
		$this->assertEquals( $action->get_group(), $retrieved->get_group() );
		$this->assertTrue( $retrieved->is_finished() );
		$this->assertEquals( \ActionScheduler_Store::STATUS_FAILED, $destination->get_status( $new_id ) );

		// ensure that the record in the old store does not exist
		$old_action = $source->fetch_action( $action_id );
		$this->assertInstanceOf( 'ActionScheduler_NullAction', $old_action );
	}

	public function test_migrate_canceled_action_from_wpPost_to_db() {
		$source = new ActionScheduler_wpPostStore();
		$destination = new ActionScheduler_DBStore();
		$migrator = new ActionMigrator( $source, $destination, $this->get_log_migrator() );

		$time      = as_get_datetime_object();
		$schedule  = new ActionScheduler_SimpleSchedule( $time );
		$action    = new ActionScheduler_Action( 'my_hook', [], $schedule, 'my_group' );
		$action_id = $source->save_action( $action );
		$source->cancel_action( $action_id );

		$new_id = $migrator->migrate( $action_id );

		// ensure we get the same record out of the new store as we stored in the old
		$retrieved = $destination->fetch_action( $new_id );
		$this->assertEquals( $action->get_hook(), $retrieved->get_hook() );
		$this->assertEqualSets( $action->get_args(), $retrieved->get_args() );
		$this->assertEquals( $action->get_schedule()->get_date()->format( 'U' ), $retrieved->get_schedule()->get_date()->format( 'U' ) );
		$this->assertEquals( $action->get_group(), $retrieved->get_group() );
		$this->assertTrue( $retrieved->is_finished() );
		$this->assertEquals( \ActionScheduler_Store::STATUS_CANCELED, $destination->get_status( $new_id ) );

		// ensure that the record in the old store does not exist
		$old_action = $source->fetch_action( $action_id );
		$this->assertInstanceOf( 'ActionScheduler_NullAction', $old_action );
	}

	private function get_log_migrator() {
		return new LogMigrator( \ActionScheduler::logger(), new ActionScheduler_DBLogger() );
	}
}