<?php

/**
 * Class TaskScheduler_Logger
 * @codeCoverageIgnore
 */
abstract class TaskScheduler_Logger {
	private static $logger = NULL;

	/**
	 * @return TaskScheduler_Logger
	 */
	public static function instance() {
		if ( empty(self::$logger) ) {
			$class = apply_filters('task_scheduler_logger_class', 'TaskScheduler_wpCommentLogger');
			self::$logger = new $class();
		}
		return self::$logger;
	}

	/**
	 * @param string $job_id
	 * @param string $message
	 * @param DateTime $date
	 *
	 * @return string The log entry ID
	 */
	abstract public function log( $job_id, $message, DateTime $date = NULL );

	/**
	 * @param string $entry_id
	 * @return TaskScheduler_LogEntry
	 */
	abstract public function get_entry( $entry_id );

	/**
	 * @param string $job_id
	 * @return TaskScheduler_LogEntry[]
	 */
	abstract public function get_logs( $job_id );

	abstract public function init();

}
 