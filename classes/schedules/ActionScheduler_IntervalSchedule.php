<?php

/**
 * Class ActionScheduler_IntervalSchedule
 */
class ActionScheduler_IntervalSchedule extends ActionScheduler_Abstract_RecurringSchedule implements ActionScheduler_Schedule {

	/**
	 * Deprecated property @see $this->__wakeup() for details.
	 **/
	private $start_timestamp = NULL;

	/**
	 * Deprecated property @see $this->__wakeup() for details.
	 **/
	private $interval_in_seconds = NULL;

	/**
	 * Calculate when this schedule should start after a given date & time using
	 * the number of seconds between recurrences.
	 *
	 * @param DateTime $after
	 * @return DateTime
	 */
	protected function calculate_next( DateTime $after ) {
		$after->modify( '+' . (int) $this->get_recurrence() . ' seconds' );
		return $after;
	}

	/**
	 * @return int
	 */
	public function interval_in_seconds() {
		_deprecated_function( __METHOD__, '3.0.0', '(int)ActionScheduler_Abstract_RecurringSchedule::get_recurrence()' );
		return (int) $this->get_recurrence();
	}

	/**
	 * Unserialize recurring schedules serialized/stored prior to AS 3.0.0
	 *
	 * Prior to Action Scheduler 3.0.0, schedules used different property names to refer
	 * to equivalent data. For example, ActionScheduler_IntervalSchedule::start_timestamp
	 * was the same as ActionScheduler_SimpleSchedule::timestamp. Action Scheduler 3.0.0
	 * aligned properties and property names for better inheritance. To maintain backward
	 * compatibility with schedules serialized and stored prior to 3.0, we need to correctly
	 * map the old property names with matching visibility.
	 */
	public function __wakeup() {
		if ( ! is_null( $this->start_timestamp ) ) {
			$this->scheduled_timestamp = $this->start_timestamp;
			unset( $this->start_timestamp );
		}

		if ( ! is_null( $this->interval_in_seconds ) ) {
			$this->recurrence = $this->interval_in_seconds;
			unset( $this->interval_in_seconds );
		}
		parent::__wakeup();
	}
}
