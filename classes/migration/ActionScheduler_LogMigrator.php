<?php


namespace Action_Scheduler\Migration;

use ActionScheduler_Logger;

class ActionScheduler_LogMigrator {
	private $source;
	private $destination;

	public function __construct( ActionScheduler_Logger $source_logger, ActionScheduler_Logger $destination_Logger ) {
		$this->source      = $source_logger;
		$this->destination = $destination_Logger;
	}

	public function migrate( $source_action_id, $destination_action_id ) {
		$logs = $this->source->get_logs( $source_action_id );
		foreach ( $logs as $log ) {
			if ( $log->get_action_id() == $source_action_id ) {
				$this->destination->log( $destination_action_id, $log->get_message(), $log->get_date() );
			}
		}
	}
}
