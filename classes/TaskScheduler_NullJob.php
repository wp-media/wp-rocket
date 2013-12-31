<?php

/**
 * Class TaskScheduler_NullJob
 */
class TaskScheduler_NullJob extends TaskScheduler_Job {

	public function __construct( $hook = '', array $args = array(), TaskScheduler_Schedule $schedule = NULL ) {
		$this->set_schedule( new TaskScheduler_NullSchedule() );
	}

	public function execute() {
		// don't execute
	}
}
 