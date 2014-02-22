<?php

/**
 * Class ActionScheduler_CronSchedule
 */
class ActionScheduler_CronSchedule implements ActionScheduler_Schedule {
	/** @var DateTime */
	private $start = NULL;
	private $start_timestamp = 0;
	/** @var CronExpression */
	private $cron = NULL;

	public function __construct( DateTime $start, CronExpression $cron ) {
		$this->start = $start;
		$this->cron = $cron;
	}

	/**
	 * @param DateTime $after
	 * @return DateTime|null
	 */
	public function next( DateTime $after = NULL ) {
		$after = empty($after) ? clone($this->start) : clone($after);
		return $this->cron->getNextRunDate($after, 0, TRUE);
	}


	/**
	 * For PHP 5.2 compat, since DateTime objects can't be serialized
	 * @return array
	 */
	public function __sleep() {
		$this->start_timestamp = $this->start->format('U');
		return array(
			'start_timestamp',
			'cron'
		);
	}

	public function __wakeup() {
		$this->start = new DateTime('@'.$this->start_timestamp);
	}
}
 