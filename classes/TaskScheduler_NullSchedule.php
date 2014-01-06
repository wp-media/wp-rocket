<?php

/**
 * Class TaskScheduler_NullSchedule
 */
class TaskScheduler_NullSchedule implements TaskScheduler_Schedule {

	public function next( DateTime $after = NULL ) {
		return NULL;
	}
}
 