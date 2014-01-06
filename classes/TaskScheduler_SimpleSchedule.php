<?php

/**
 * Class TaskScheduler_SimpleSchedule
 */
class TaskScheduler_SimpleSchedule implements TaskScheduler_Schedule {
	private $date = NULL;
	public function __construct( DateTime $date ) {
		$this->date = clone($date);
	}

	/**
	 * @param DateTime $after
	 *
	 * @return DateTime|null
	 */
	public function next( DateTime $after = NULL ) {
		$after = empty($after) ? new DateTime('@0') : $after;
		return ( $after > $this->date ) ? NULL : clone( $this->date );
	}
}
 