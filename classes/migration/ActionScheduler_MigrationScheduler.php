<?php


namespace Action_Scheduler\Migration;

use ActionScheduler_Data;

class ActionScheduler_MigrationScheduler {
	const STATUS_FLAG     = 'action_scheduler_migration_status';
	const STATUS_COMPLETE = 'complete';
	const HOOK            = 'action_scheduler/migration_hook';
	const GROUP           = 'action-scheduler-DB-tables';
	const MIN_PHP_VERSION = '5.5';

	/**
	 * Set up the callback for the scheduled job
	 */
	public function hook() {
		add_action( self::HOOK, array( $this, 'run_migration' ), 10, 0 );
	}

	/**
	 * Remove the callback for the scheduled job
	 */
	public function unhook() {
		remove_action( self::HOOK, array( $this, 'run_migration' ), 10 );
	}

	/**
	 * The migration callback.
	 */
	public function run_migration() {
		$migration_runner = $this->get_migration_runner();
		$count            = $migration_runner->run( $this->get_batch_size() );

		if ( $count === 0 ) {
			$this->mark_complete();
		} else {
			$this->schedule_migration( time() + $this->get_schedule_interval() );
		}
	}

	public function mark_complete() {
		$migration_runner   = $this->get_migration_runner();
		$destination_store  = $migration_runner->get_destination_store();
		$destination_logger = $migration_runner->get_destination_logger();

		$action_id = $destination_store->find_action( self::HOOK );
		if ( $action_id ) {
			$destination_logger->hook_stored_action();
			$destination_store->mark_complete( $action_id );
		}

		$this->unschedule_migration();

		update_option( self::STATUS_FLAG, self::STATUS_COMPLETE );
		do_action( 'action_scheduler/migration_complete' );
	}

	/**
	 * @return bool Whether the flag has been set marking the migration as complete
	 */
	public function is_migration_complete() {
		return get_option( self::STATUS_FLAG ) === self::STATUS_COMPLETE;
	}

	/**
	 * @return bool Whether there is a pending action in the store to handle the migration
	 */
	public function is_migration_scheduled() {
		$next = as_next_scheduled_action( self::HOOK );

		return ! empty( $next );
	}

	/**
	 * @param int $when Timestamp to run the next migration batch. Defaults to now.
	 *
	 * @return string The action ID
	 */
	public function schedule_migration( $when = 0 ) {
		$next = as_next_scheduled_action( self::HOOK );

		if ( ! empty( $next ) ) {
			return $next;
		}

		if ( empty( $when ) ) {
			$when = time();
		}

		return as_schedule_single_action( $when, self::HOOK, array(), self::GROUP );
	}

	/**
	 * Removes the scheduled migration action
	 */
	public function unschedule_migration() {
		as_unschedule_action( self::HOOK, null, self::GROUP );
	}

	/**
	 * @return bool Environment dependencies met for database data store.
	 */
	public function dependencies_met() {
		return version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '>=' );
	}

	/**
	 * @return int Seconds between migration runs. Defaults to two minutes.
	 */
	private function get_schedule_interval() {
		return (int) apply_filters( 'action_scheduler/migration_interval', 2 * MINUTE_IN_SECONDS );
	}

	/**
	 * @return int Number of actions to migrate in each batch. Defaults to 1000.
	 */
	private function get_batch_size() {
		return (int) apply_filters( 'action_scheduler/migration_batch_size', 1000 );
	}

	/**
	 * @return ActionScheduler_MigrationRunner
	 */
	private function get_migration_runner() {
		$config = ActionScheduler_Data::instance()->get_migration_config_object();

		return new ActionScheduler_MigrationRunner( $config );
	}

}
