<?php

/**
 * Class ActionScheduler_CronSchedule
 */
class ActionScheduler_CronSchedule extends ActionScheduler_Abstract_RecurringSchedule implements ActionScheduler_Schedule {

	/**
	 * Deprecated property @see $this->__wakeup() for details.
	 **/
	private $start_timestamp = NULL;

	/**
	 * Deprecated property @see $this->__wakeup() for details.
	 **/
	private $cron = NULL;

	/**
	 * Wrapper for parent constructor to accept a cron expression string and map it to a CronExpression for this
	 * objects $recurrence property.
	 *
	 * @param DateTime $start The date & time to run the action.
	 * @param CronExpression|string $recurrence The CronExpression used to calculate the schedule's next instance.
	 * @param DateTime|null $first (Optional) The date & time the first instance of this interval schedule ran. Default null, meaning this is the first instance.
	 */
	public function __construct( DateTime $start, $recurrence, DateTime $first = null ) {
		if ( ! is_a( $recurrence, 'CronExpression' ) ) {
			$recurrence = CronExpression::factory( $recurrence );
		}

		// For backward compatibility, we need to make sure the date is set to the first matching cron date, not whatever date is passed in. Importantly, by passing true as the 3rd param, if $start matches the cron expression, then it will be used. This was previously handled in the now deprecated next() method.
		$date = $recurrence->getNextRunDate( $start, 0, true );

		// parent::__construct() will set this to $date by default, but that may be different to $start now.
		$first = empty( $first ) ? $start : $first;

		parent::__construct( $date, $recurrence, $first );
	}

	/**
	 * Calculate when an instance of this schedule would start based on a given
	 * date & time using its the CronExpression.
	 *
	 * @param DateTime $after
	 * @return DateTime
	 */
	protected function calculate_next( DateTime $after ) {
		return $this->recurrence->getNextRunDate( $after, 0, false );
	}

	/**
	 * @return string
	 */
	public function get_recurrence() {
		return strval( $this->recurrence );
	}

	/**
	 * Unserialize cron schedules serialized/stored prior to AS 3.0.0
	 *
	 * For more background, @see ActionScheduler_Abstract_RecurringSchedule::__wakeup().
	 */
	public function __wakeup() {
		if ( ! is_null( $this->start_timestamp ) ) {
			$this->scheduled_timestamp = $this->start_timestamp;
			unset( $this->start_timestamp );
		}

		if ( ! is_null( $this->cron ) ) {
			$this->recurrence = $this->cron;
		}
		parent::__wakeup();
	}
}

