<?php

/**
 * Class ActionScheduler_LogEntry
 */
class ActionScheduler_LogEntry {
	protected $action_id =  '';
	protected $message =  '';
	protected $date;

	/**
	 * Constructor
	 *
	 * @param mixed  $action_id	Action ID
	 * @param string $message   Message
	 * @param string $date
	 */
	public function __construct( $action_id, $message, $date ) {
		$this->action_id = $action_id;
		$this->message   = $message;
		$this->date      =  $date;
	}

	/**
	 * Returns the date when this log entry was created
	 *
	 * The date is returned in a string, and there is a default format but it can be changed
	 * through the `action_scheduler_date_format` filter.
	 *
	 * The date is the same timezone as the WordPress site.
	 *
	 * @return string
	 */
	public function get_date() {
		$date = as_get_datetime_object( $this->date );
		$date->setTimezone( ActionScheduler_TimezoneHelper::get_local_timezone() );
		return $date->format( apply_filters( 'action_scheduler_date_format', 'Y-m-d H:i:s' ) );
	}

	public function get_action_id() {
		return $this->action_id;
	}

	public function get_message() {
		return $this->message;
	}
}
 
