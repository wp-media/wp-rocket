<?php


namespace Action_Scheduler\Migration;


class ActionScheduler_ActionMigrator {
	private $source;
	private $destination;
	private $log_migrator;

	public function __construct( \ActionScheduler_Store $source_store, \ActionScheduler_Store $destination_store, ActionScheduler_LogMigrator $log_migrator ) {
		$this->source       = $source_store;
		$this->destination  = $destination_store;
		$this->log_migrator = $log_migrator;
	}

	public function migrate( $source_action_id ) {
		$action = $this->source->fetch_action( $source_action_id );

		try {
			$status = $this->source->get_status( $source_action_id );
		} catch ( \Exception $e ) {
			$status = '';
		}

		if ( empty( $status ) || ! $action->get_schedule()->next() ) {
			// empty status means the action didn't exist
			// null schedule means it's missing vital data
			// delete it and move on
			try {
				$this->source->delete_action( $source_action_id );
			} catch ( \Exception $e ) {
				// nothing to do, it didn't exist in the first place
			}
			do_action( 'action_scheduler/no_action_to_migrate', $source_action_id, $this->source, $this->destination );

			return 0;
		}

		try {

			// Make sure the last attempt date is set correctly for completed and failed actions
			$last_attempt_date = ( $status !== \ActionScheduler_Store::STATUS_PENDING ) ? $this->source->get_date( $source_action_id ) : null;

			$destination_action_id = $this->destination->save_action( $action, null, $last_attempt_date );
		} catch ( \Exception $e ) {
			do_action( 'action_scheduler/migrate_action_failed', $source_action_id, $this->source, $this->destination );

			return 0; // could not save the action in the new store
		}


		try {
			if ( $status === \ActionScheduler_Store::STATUS_FAILED ) {
				$this->destination->mark_failure( $destination_action_id );
			}

			$this->log_migrator->migrate( $source_action_id, $destination_action_id );
			$this->source->delete_action( $source_action_id );

			do_action( 'action_scheduler/migrated_action', $source_action_id, $destination_action_id, $this->source, $this->destination );

			return $destination_action_id;
		} catch ( \Exception $e ) {
			// could not delete from the old store
			do_action( 'action_scheduler/migrate_action_incomplete', $source_action_id, $destination_action_id, $this->source, $this->destination );
			do_action( 'action_scheduler/migrated_action', $source_action_id, $destination_action_id, $this->source, $this->destination );

			return $destination_action_id;
		}
	}
}