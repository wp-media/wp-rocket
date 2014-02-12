<?php

/**
 * Class ActionScheduler_wpCommentLogger_Test
 * @package test_cases\logging
 */
class ActionScheduler_wpCommentLogger_Test extends ActionScheduler_UnitTestCase {
	public function test_default_logger() {
		$logger = ActionScheduler::logger();
		$this->assertInstanceOf( 'ActionScheduler_Logger', $logger );
		$this->assertInstanceOf( 'ActionScheduler_wpCommentLogger', $logger );
	}

	public function test_add_log_entry() {
		$action_id = wc_schedule_single_action( time(), 'a hook' );
		$logger = ActionScheduler::logger();
		$message = 'Logging that something happened';
		$log_id = $logger->log( $action_id, $message );
		$entry = $logger->get_entry( $log_id );

		$this->assertEquals( $action_id, $entry->get_action_id() );
		$this->assertEquals( $message, $entry->get_message() );
	}

	public function test_null_log_entry() {
		$logger = ActionScheduler::logger();
		$entry = $logger->get_entry( 1 );
		$this->assertEquals( '', $entry->get_action_id() );
		$this->assertEquals( '', $entry->get_message() );
	}

	public function test_erroneous_entry_id() {
		$comment = wp_insert_comment(array(
			'comment_post_ID' => 1,
			'comment_author' => 'test',
			'comment_content' => 'this is not a log entry',
		));
		$logger = ActionScheduler::logger();
		$entry = $logger->get_entry( $comment );
		$this->assertEquals( '', $entry->get_action_id() );
		$this->assertEquals( '', $entry->get_message() );
	}

	public function test_storage_comments() {
		$action_id = wc_schedule_single_action( time(), 'a hook' );
		$logger = ActionScheduler::logger();
		$logs = $logger->get_logs( $action_id );
		$expected = new ActionScheduler_LogEntry( $action_id, 'action created' );
		$this->assertTrue( in_array( $expected, $logs ) );
	}

	public function test_execution_comments() {
		$action_id = wc_schedule_single_action( time(), 'a hook' );
		$logger = ActionScheduler::logger();
		$started = new ActionScheduler_LogEntry( $action_id, 'action started' );
		$finished = new ActionScheduler_LogEntry( $action_id, 'action complete' );

		$runner = new ActionScheduler_QueueRunner();
		$runner->run();

		$logs = $logger->get_logs( $action_id );
		$this->assertTrue( in_array( $started, $logs ) );
		$this->assertTrue( in_array( $finished, $logs ) );
	}

	public function test_failed_execution_comments() {
		$hook = md5(rand());
		add_action( $hook, array( $this, '_a_hook_callback_that_throws_an_exception' ) );
		$action_id = wc_schedule_single_action( time(), $hook );
		$logger = ActionScheduler::logger();
		$started = new ActionScheduler_LogEntry( $action_id, 'action started' );
		$finished = new ActionScheduler_LogEntry( $action_id, 'action complete' );
		$failed = new ActionScheduler_LogEntry( $action_id, 'action failed: Execution failed' );

		$runner = new ActionScheduler_QueueRunner();
		$runner->run();

		$logs = $logger->get_logs( $action_id );
		$this->assertTrue( in_array( $started, $logs ) );
		$this->assertFalse( in_array( $finished, $logs ) );
		$this->assertTrue( in_array( $failed, $logs ) );
	}

	public function test_canceled_action_comments() {
		$action_id = wc_schedule_single_action( time(), 'a hook' );
		wc_unschedule_action( 'a hook' );
		$logger = ActionScheduler::logger();
		$logs = $logger->get_logs( $action_id );
		$expected = new ActionScheduler_LogEntry( $action_id, 'action canceled' );
		$this->assertTrue( in_array( $expected, $logs ) );
	}

	public function _a_hook_callback_that_throws_an_exception() {
		throw new RuntimeException('Execution failed');
	}
}
 