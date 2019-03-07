<?php


namespace Action_Scheduler\Custom_Tables\Migration;


use Action_Scheduler\Custom_Tables\Plugin;

class ActionScheduler_MigrationScheduler {
	const STATUS_FLAG     = 'action_scheduler_custom_tables_migration_status';
	const STATUS_COMPLETE = 'complete';
	const HOOK            = 'action_scheduler/custom_tables/migration_hook';
	const GROUP           = 'action-scheduler-custom-tables';

	/**
	 * Set up the callback for the scheduled job
	 */
	public function hook() {
		add_action( self::HOOK, [ $this, 'run_migration' ], 10, 0 );
	}

	/**
	 * Remove the callback for the scheduled job
	 */
	public function unhook() {
		remove_action( self::HOOK, [ $this, 'run_migration' ], 10 );
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
		$this->unschedule_migration();
		update_option( self::STATUS_FLAG, self::STATUS_COMPLETE );
		do_action( 'action_scheduler/custom_tables/migration_complete' );
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
		if ( empty( $when ) ) {
			$when = time();
		}

		return as_schedule_single_action( $when, self::HOOK, [], self::GROUP );
	}

	/**
	 * Removes the scheduled migration action
	 */
	public function unschedule_migration() {
		as_unschedule_action( self::HOOK, null, self::GROUP );
	}

	/**
	 * @return int Seconds between migration runs. Defaults to two minutes.
	 */
	private function get_schedule_interval() {
		return (int) apply_filters( 'action_scheduler/custom_tables/migration_interval', 2 * MINUTE_IN_SECONDS );
	}

	/**
	 * @return int Number of actions to migrate in each batch. Defaults to 1000.
	 */
	private function get_batch_size() {
		return (int) apply_filters( 'action_scheduler/custom_tables/migration_batch_size', 1000 );
	}

	/**
	 * @return ActionScheduler_MigrationRunner
	 */
	private function get_migration_runner() {
		$config = Plugin::instance()->get_migration_config_object();

		return new ActionScheduler_MigrationRunner( $config );
	}

}
