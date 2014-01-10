<?php

/**
 * Class TaskScheduler_Job
 */
class TaskScheduler_Job {
	protected $hook = '';
	protected $args = array();
	/** @var TaskScheduler_Schedule */
	protected $schedule = NULL;
	protected $group = '';

	public function __construct( $hook, array $args = array(), TaskScheduler_Schedule $schedule = NULL, $group = '' ) {
		$schedule = empty( $schedule ) ? new TaskScheduler_NullSchedule() : $schedule;
		$this->set_hook($hook);
		$this->set_schedule($schedule);
		$this->set_args($args);
		$this->set_group($group);
	}

	public function execute() {
		return do_action_ref_array($this->get_hook(), $this->get_args());
	}

	/**
	 * @param string $hook
	 * @return void
	 */
	protected function set_hook( $hook ) {
		$this->hook = $hook;
	}

	public function get_hook() {
		return $this->hook;
	}

	protected function set_schedule( TaskScheduler_Schedule $schedule ) {
		$this->schedule = $schedule;
	}

	/**
	 * @return TaskScheduler_Schedule
	 */
	public function get_schedule() {
		return $this->schedule;
	}

	protected function set_args( array $args ) {
		$this->args = $args;
	}

	public function get_args() {
		return $this->args;
	}

	/**
	 * @param string $group
	 */
	protected function set_group( $group ) {
		$this->group = $group;
	}

	/**
	 * @return string
	 */
	public function get_group() {
		return $this->group;
	}

	/**
	 * @return bool If the job has been finished
	 */
	public function is_finished() {
		return FALSE;
	}
}
 