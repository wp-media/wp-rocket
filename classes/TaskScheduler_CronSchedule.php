<?php

/**
 * Class TaskScheduler_CronSchedule
 */
class TaskScheduler_CronSchedule implements TaskScheduler_Schedule {
	/** @var DateTime */
	private $start = NULL;
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
}
 