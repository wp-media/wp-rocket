<?php

/**
 * Class ActionScheduler_LogEntry
 */
class ActionScheduler_LogEntry {
	protected $action_id = '';
	protected $message = '';

	public function __construct( $action_id, $message ) {
		$this->action_id = $action_id;
		$this->message = $message;
	}

	public function get_action_id() {
		return $this->action_id;
	}

	public function get_message() {
		return $this->message;
	}
}
 