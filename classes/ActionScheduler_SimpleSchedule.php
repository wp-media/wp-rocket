<?php

/**
 * Class ActionScheduler_SimpleSchedule
 */
class ActionScheduler_SimpleSchedule implements ActionScheduler_Schedule {
	private $date = NULL;
	private $timestamp = 0;
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

	/**
	 * For PHP 5.2 compat, since DateTime objects can't be serialized
	 * @return array
	 */
	public function __sleep() {
		$this->timestamp = $this->date->format('U');
		return array(
			'timestamp',
		);
	}

	public function __wakeup() {
		$this->date = new DateTime('@'.$this->timestamp);
	}
}
 