<?php

/**
 * Class ActionScheduler_IntervalSchedule
 */
class ActionScheduler_IntervalSchedule implements ActionScheduler_Schedule {
	/** @var DateTime */
	private $first = NULL;
	private $start = NULL;
	private $first_timestamp = 0;
	private $start_timestamp = 0;
	private $interval_in_seconds = 0;

	public function __construct( DateTime $first, $interval ) {
		$this->first = $first;
		$this->start = $first;
		$this->interval_in_seconds = (int)$interval;
	}

	/**
	 * @param DateTime $after
	 *
	 * @return DateTime|null
	 */
	public function next( DateTime $after = NULL ) {
		$after = empty($after) ? as_get_datetime_object('@0') : clone $after;
		if ( $after > $this->first ) {
			$after->modify('+'.$this->interval_in_seconds.' seconds');
			return $after;
		}
		return clone $this->first;
	}

	/**
	 * @return DateTime
	 */
	public function get_start() {
		return $this->start;
	}

	/**
	 * @param DateTime $next
	 */
	public function set_next( DateTime $next ) {
		$this->start = $next;
	}

	/**
	 * @return bool
	 */
	public function is_recurring() {
		return true;
	}

	/**
	 * @return int
	 */
	public function interval_in_seconds() {
		return $this->interval_in_seconds;
	}

	/**
	 * For PHP 5.2 compat, since DateTime objects can't be serialized
	 * @return array
	 */
	public function __sleep() {
		$this->first_timestamp = $this->first->getTimestamp();
		$this->start_timestamp = $this->start->getTimestamp();
		return array(
			'first_timestamp',
			'start_timestamp',
			'interval_in_seconds'
		);
	}

	public function __wakeup() {
		$this->first = as_get_datetime_object($this->first_timestamp);
		$this->start = as_get_datetime_object($this->start_timestamp);
	}
}
