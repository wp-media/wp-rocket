<?php

/**
 * Class TaskScheduler_JobFactory
 */
class TaskScheduler_JobFactory {
	/**
	 * @param string $hook The hook to trigger when this task runs
	 * @param array $args Args to pass when the hook is triggered
	 * @param int $when Unix timestamp when the task will run
	 * @param string $group A group to put the job in
	 *
	 * @return string The ID of the stored job
	 */
	public function single( $hook, $args = array(), $when = NULL, $group = '' ) {
		$date = $this->get_date_object( $when );
		$schedule = new TaskScheduler_SimpleSchedule( $date );
		$task = new TaskScheduler_Job( $hook, $args, $schedule, $group );
		return $this->store( $task );
	}

	/**
	 * @param string $hook The hook to trigger when this task runs
	 * @param array $args Args to pass when the hook is triggered
	 * @param int $first Unix timestamp for the first run
	 * @param int $interval Seconds between runs
	 * @param string $group A group to put the job in
	 *
	 * @return string The ID of the stored job
	 */
	public function recurring( $hook, $args = array(), $first = NULL, $interval = NULL, $group = '' ) {
		if ( empty($interval) ) {
			return $this->single( $hook, $args, $first, $group );
		}
		$date = $this->get_date_object( $first );
		$schedule = new TaskScheduler_IntervalSchedule( $date, $interval );
		$task = new TaskScheduler_Job( $hook, $args, $schedule, $group );
		return $this->store( $task );
	}


	/**
	 * @param string $hook The hook to trigger when this task runs
	 * @param array $args Args to pass when the hook is triggered
	 * @param int $first Unix timestamp for the first run
	 * @param int $schedule A cron definition string
	 * @param string $group A group to put the job in
	 *
	 * @return string The ID of the stored job
	 */
	public function cron( $hook, $args = array(), $first = NULL, $schedule = NULL, $group = '' ) {
		if ( empty($schedule) ) {
			return $this->single( $hook, $args, $first, $group );
		}
		$date = $this->get_date_object( $first );
		$cron = CronExpression::factory( $schedule );
		$schedule = new TaskScheduler_CronSchedule( $date, $cron );
		$task = new TaskScheduler_Job( $hook, $args, $schedule, $group );
		return $this->store( $task );
	}

	/**
	 * Create a DateTime object out of the parameter
	 *
	 * @param int|string|DateTime $when
	 * @return DateTime
	 */
	protected function get_date_object( $when ) {
		$when = empty($when) ? time() : $when;
		if ( is_object($when) && $when instanceof DateTime ) {
			$date = $when;
		} elseif ( is_numeric( $when ) ) {
			$date = new DateTime( '@'.$when );
		} else {
			$date = new DateTime( $when );
		}
		return $date;
	}

	/**
	 * @param TaskScheduler_Job $job
	 * @return string The ID of the stored job
	 */
	protected function store( TaskScheduler_Job $job ) {
		$store = TaskScheduler_JobStore::instance();
		return $store->save_job( $job );
	}
}
 