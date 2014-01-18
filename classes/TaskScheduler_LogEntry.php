<?php

/**
 * Class TaskScheduler_LogEntry
 */
class TaskScheduler_LogEntry {
	protected $job_id = '';
	protected $message = '';

	public function __construct( $job_id, $message ) {
		$this->job_id = $job_id;
		$this->message = $message;
	}

	public function get_job_id() {
		return $this->job_id;
	}

	public function get_message() {
		return $this->message;
	}
}
 