<?php

/**
 * Class TaskScheduler_wpCommentLogger_Test
 * @package test_cases\logging
 */
class TaskScheduler_wpCommentLogger_Test extends TaskScheduler_UnitTestCase {
	public function test_default_logger() {
		$logger = TaskScheduler::logger();
		$this->assertInstanceOf( 'TaskScheduler_Logger', $logger );
		$this->assertInstanceOf( 'TaskScheduler_wpCommentLogger', $logger );
	}

	public function test_add_log_entry() {
		$job_id = schedule_single_task( time(), 'a hook' );
		$logger = TaskScheduler::logger();
		$message = 'Logging that something happened';
		$log_id = $logger->log( $job_id, $message );
		$entry = $logger->get_entry( $log_id );

		$this->assertEquals( $job_id, $entry->get_job_id() );
		$this->assertEquals( $message, $entry->get_message() );
	}

	public function test_null_log_entry() {
		$logger = TaskScheduler::logger();
		$entry = $logger->get_entry( 1 );
		$this->assertEquals( '', $entry->get_job_id() );
		$this->assertEquals( '', $entry->get_message() );
	}

	public function test_erroneous_entry_id() {
		$comment = wp_insert_comment(array(
			'comment_post_ID' => 1,
			'comment_author' => 'test',
			'comment_content' => 'this is not a log entry',
		));
		$logger = TaskScheduler::logger();
		$entry = $logger->get_entry( $comment );
		$this->assertEquals( '', $entry->get_job_id() );
		$this->assertEquals( '', $entry->get_message() );
	}

	public function test_storage_comments() {
		$job_id = schedule_single_task( time(), 'a hook' );
		$logger = TaskScheduler::logger();
		$logs = $logger->get_logs( $job_id );
		$expected = new TaskScheduler_LogEntry( $job_id, 'job created' );
		$this->assertTrue( in_array( $expected, $logs ) );
	}

	public function test_execution_comments() {
		$job_id = schedule_single_task( time(), 'a hook' );
		$logger = TaskScheduler::logger();
		$started = new TaskScheduler_LogEntry( $job_id, 'job started' );
		$finished = new TaskScheduler_LogEntry( $job_id, 'job complete' );

		$runner = new TaskScheduler_JobRunner();
		$runner->run();

		$logs = $logger->get_logs( $job_id );
		$this->assertTrue( in_array( $started, $logs ) );
		$this->assertTrue( in_array( $finished, $logs ) );
	}

	public function test_failed_execution_comments() {
		$hook = md5(rand());
		add_action( $hook, array( $this, '_a_hook_callback_that_throws_an_exception' ) );
		$job_id = schedule_single_task( time(), $hook );
		$logger = TaskScheduler::logger();
		$started = new TaskScheduler_LogEntry( $job_id, 'job started' );
		$finished = new TaskScheduler_LogEntry( $job_id, 'job complete' );
		$failed = new TaskScheduler_LogEntry( $job_id, 'job failed: Execution failed' );

		$runner = new TaskScheduler_JobRunner();
		$runner->run();

		$logs = $logger->get_logs( $job_id );
		$this->assertTrue( in_array( $started, $logs ) );
		$this->assertFalse( in_array( $finished, $logs ) );
		$this->assertTrue( in_array( $failed, $logs ) );
	}

	public function _a_hook_callback_that_throws_an_exception() {
		throw new RuntimeException('Execution failed');
	}
}
 