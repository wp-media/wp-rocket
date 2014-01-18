<?php

/**
 * Class TaskScheduler_wpCommentLogger
 */
class TaskScheduler_wpCommentLogger extends TaskScheduler_Logger {
	const AGENT = 'TaskScheduler';
	const TYPE = 'task_log';

	/**
	 * @param string $job_id
	 * @param string $message
	 * @param DateTime $date
	 *
	 * @return string The log entry ID
	 */
	public function log( $job_id, $message, DateTime $date = NULL ) {
		if ( empty($date) ) {
			$date = new DateTime();
		}
		$comment_id = $this->create_wp_comment( $job_id, $message, $date );
		return $comment_id;
	}

	protected function create_wp_comment( $job_id, $message, DateTime $date ) {
		$comment_data = array(
			'comment_post_ID' => $job_id,
			'comment_date' => $date->format('Y-m-d H:i:s'),
			'comment_author' => self::AGENT,
			'comment_content' => $message,
			'comment_agent' => self::AGENT,
			'comment_type' => self::TYPE,
		);
		return wp_insert_comment($comment_data);
	}

	/**
	 * @param string $entry_id
	 * @return TaskScheduler_LogEntry
	 */
	public function get_entry( $entry_id ) {
		$comment = $this->get_comment( $entry_id );
		if ( empty($comment) || $comment->comment_type != self::TYPE ) {
			return new TaskScheduler_NullLogEntry();
		}
		return new TaskScheduler_LogEntry( $comment->comment_post_ID, $comment->comment_content, $comment->comment_type );
	}

	/**
	 * @param string $job_id
	 * @return TaskScheduler_LogEntry[]
	 */
	public function get_logs( $job_id ) {
		$comments = get_comments(array(
			'post_ID' => $job_id,
			'orderby' => 'comment_date_gmt',
			'order' => 'ASC',
			'type' => self::TYPE,
		));
		$logs = array();
		foreach ( $comments as $c ) {
			$entry = $this->get_entry( $c );
			if ( !empty($entry) ) {
				$logs[] = $entry;
			}
		}
		return $logs;
	}

	protected function get_comment( $comment_id ) {
		return get_comment( $comment_id );
	}

	public function init() {
		add_action( 'task_scheduler_stored_job', array( $this, 'log_stored_job' ), 10, 1 );
		add_action( 'task_scheduler_before_execute', array( $this, 'log_started_job' ), 10, 1 );
		add_action( 'task_scheduler_after_execute', array( $this, 'log_completed_job' ), 10, 1 );
		add_action( 'task_scheduler_failed_execution', array( $this, 'log_failed_job' ), 10, 2 );
	}

	public function log_stored_job( $job_id ) {
		$this->log( $job_id, __('job created', 'task-scheduler') );
	}

	public function log_started_job( $job_id ) {
		$this->log( $job_id, __('job started', 'task-scheduler') );
	}

	public function log_completed_job( $job_id ) {
		$this->log( $job_id, __('job complete', 'task-scheduler') );
	}

	public function log_failed_job( $job_id, Exception $exception ) {
		$this->log( $job_id, sprintf(__('job failed: %s', 'task-scheduler'), $exception->getMessage() ));
	}

}
 