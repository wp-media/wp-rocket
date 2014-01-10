<?php

/**
 * Class TaskScheduler_FinishedJob
 */
class TaskScheduler_FinishedJob extends TaskScheduler_Job {

	public function execute() {
		// don't execute
	}

	public function is_finished() {
		return TRUE;
	}
}
 