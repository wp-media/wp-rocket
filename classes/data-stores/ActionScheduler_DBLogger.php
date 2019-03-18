<?php

class ActionScheduler_DBLogger extends ActionScheduler_Logger {

	/**
	 * @param string   $action_id
	 * @param string   $message
	 * @param DateTime $date
	 *
	 * @return string The log entry ID
	 */
	public function log( $action_id, $message, DateTime $date = null ) {
		if ( empty( $date ) ) {
			$date = as_get_datetime_object();
		} else {
			$date = clone $date;
		}

		$date_gmt = $date->format( 'Y-m-d H:i:s' );
		ActionScheduler_TimezoneHelper::set_local_timezone( $date );
		$date_local = $date->format( 'Y-m-d H:i:s' );

		/** @var \wpdb $wpdb */
		global $wpdb;
		$wpdb->insert( $wpdb->actionscheduler_logs, [
			'action_id'      => $action_id,
			'message'        => $message,
			'log_date_gmt'   => $date_gmt,
			'log_date_local' => $date_local,
		], [ '%d', '%s', '%s', '%s' ] );

		return $wpdb->insert_id;
	}

	/**
	 * @param string $entry_id
	 *
	 * @return ActionScheduler_LogEntry
	 */
	public function get_entry( $entry_id ) {
		/** @var \wpdb $wpdb */
		global $wpdb;
		$entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->actionscheduler_logs} WHERE log_id=%d", $entry_id ) );

		return $this->create_entry_from_db_record( $entry );
	}

	/**
	 * @param object $record
	 *
	 * @return ActionScheduler_LogEntry
	 */
	private function create_entry_from_db_record( $record ) {
		if ( empty( $record ) ) {
			return new ActionScheduler_NullLogEntry();
		}

		$date = as_get_datetime_object( $record->log_date_gmt );

		return new ActionScheduler_LogEntry( $record->action_id, $record->message, $date );
	}

	/**
	 * @param string $action_id
	 *
	 * @return ActionScheduler_LogEntry[]
	 */
	public function get_logs( $action_id ) {
		/** @var \wpdb $wpdb */
		global $wpdb;

		$records = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->actionscheduler_logs} WHERE action_id=%d", $action_id ) );

		return array_map( [ $this, 'create_entry_from_db_record' ], $records );
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function init() {

		$table_maker = new ActionScheduler_LoggerSchema();
		$table_maker->register_tables();

		parent::init();

		add_action( 'action_scheduler_deleted_action', [ $this, 'clear_deleted_action_logs' ], 10, 1 );
	}

	public function clear_deleted_action_logs( $action_id ) {
		/** @var \wpdb $wpdb */
		global $wpdb;
		$wpdb->delete( $wpdb->actionscheduler_logs, [ 'action_id' => $action_id, ], [ '%d' ] );
	}
}
