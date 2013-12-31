<?php

/**
 * Class TaskScheduler_NullSchedule
 */
class TaskScheduler_NullSchedule implements TaskScheduler_Schedule {

	public function next( $after = NULL ) {
		return NULL;
	}
}
 