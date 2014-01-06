<?php

/**
 * Class TaskScheduler_IntervalSchedule
 */
class TaskScheduler_IntervalSchedule implements TaskScheduler_Schedule {
	/** @var DateTime */
	private $start = NULL;
	private $interval_in_seconds = 0;

	public function __construct( DateTime $start, $interval ) {
		$this->start = $start;
		$this->interval_in_seconds = (int)$interval;
	}

	/**
	 * @param DateTime $after
	 *
	 * @return DateTime|null
	 */
	public function next( DateTime $after = NULL ) {
		$after = empty($after) ? new DateTime('@0') : clone($after);
		return ( $after > $this->start ) ? $after->modify('+'.$this->interval_in_seconds.' seconds') : clone( $this->start );
	}
}
 