<?php

/**
 * Class ActionScheduler_LogEntry
 */
class ActionScheduler_LogEntry {

	/**
	 * @var int $action_id
	 */
	protected $action_id =  '';

	/**
	 * @var string $message
	 */
	protected $message =  '';

	/**
	 * @var Datetime $date
	 */
	protected $date;

	/**
	 * Constructor
	 *
	 * @param mixed  $action_id Action ID
	 * @param string $message   Message
	 * @param Datetime $date    Datetime object with the time when this log entry was created. If this parameter is
	 *                          not provided a new Datetime object (with current time) will be created.
	 */
	public function __construct( $action_id, $message, Datetime $date = null ) {
		$this->action_id = $action_id;
		$this->message   = $message;
		$this->date      = $date ? $date : new Datetime;
	}

	/**
	 * Returns the date when this log entry was created
	 *
	 * @return Datetime
	 */
	public function get_date() {
		return $this->date;
	}

	public function get_action_id() {
		return $this->action_id;
	}

	public function get_message() {
		return $this->message;
	}
}

