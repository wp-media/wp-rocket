<?php

/**
 * Class TaskScheduler_Schedule
 */
interface TaskScheduler_Schedule {
	/**
	 * @param DateTime $after
	 * @return DateTime|null
	 */
	public function next( DateTime $after = NULL );
}
 