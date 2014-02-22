<?php

/**
 * Class ActionScheduler_ActionFactory
 */
class ActionScheduler_ActionFactory {
	/**
	 * @param string $hook The hook to trigger when this action runs
	 * @param array $args Args to pass when the hook is triggered
	 * @param int $when Unix timestamp when the action will run
	 * @param string $group A group to put the action in
	 *
	 * @return string The ID of the stored action
	 */
	public function single( $hook, $args = array(), $when = NULL, $group = '' ) {
		$date = ActionScheduler::get_datetime_object( $when );
		$schedule = new ActionScheduler_SimpleSchedule( $date );
		$action = new ActionScheduler_Action( $hook, $args, $schedule, $group );
		return $this->store( $action );
	}

	/**
	 * @param string $hook The hook to trigger when this action runs
	 * @param array $args Args to pass when the hook is triggered
	 * @param int $first Unix timestamp for the first run
	 * @param int $interval Seconds between runs
	 * @param string $group A group to put the action in
	 *
	 * @return string The ID of the stored action
	 */
	public function recurring( $hook, $args = array(), $first = NULL, $interval = NULL, $group = '' ) {
		if ( empty($interval) ) {
			return $this->single( $hook, $args, $first, $group );
		}
		$date = ActionScheduler::get_datetime_object( $first );
		$schedule = new ActionScheduler_IntervalSchedule( $date, $interval );
		$action = new ActionScheduler_Action( $hook, $args, $schedule, $group );
		return $this->store( $action );
	}


	/**
	 * @param string $hook The hook to trigger when this action runs
	 * @param array $args Args to pass when the hook is triggered
	 * @param int $first Unix timestamp for the first run
	 * @param int $schedule A cron definition string
	 * @param string $group A group to put the action in
	 *
	 * @return string The ID of the stored action
	 */
	public function cron( $hook, $args = array(), $first = NULL, $schedule = NULL, $group = '' ) {
		if ( empty($schedule) ) {
			return $this->single( $hook, $args, $first, $group );
		}
		$date = ActionScheduler::get_datetime_object( $first );
		$cron = CronExpression::factory( $schedule );
		$schedule = new ActionScheduler_CronSchedule( $date, $cron );
		$action = new ActionScheduler_Action( $hook, $args, $schedule, $group );
		return $this->store( $action );
	}

	/**
	 * @param ActionScheduler_Action $action
	 *
	 * @return string The ID of the stored action
	 */
	protected function store( ActionScheduler_Action $action ) {
		$store = ActionScheduler_Store::instance();
		return $store->save_action( $action );
	}
}
 