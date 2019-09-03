<?php

/**
 * Class ActionScheduler_DBLogger_Test
 * @package test_cases\logging
 * @group tables
 */
class ActionScheduler_DBLogger_Test extends ActionScheduler_UnitTestCase {
	public function test_default_logger() {
		$logger = ActionScheduler::logger();
		$this->assertInstanceOf( 'ActionScheduler_Logger', $logger );
		$this->assertInstanceOf( ActionScheduler_DBLogger::class, $logger );
	}

	public function test_add_log_entry() {
		$action_id = as_schedule_single_action( time(), __METHOD__ );
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

	public function test_storage_logs() {
		$action_id = as_schedule_single_action( time(), __METHOD__ );
		$logger = ActionScheduler::logger();
		$logs = $logger->get_logs( $action_id );
		$expected = new ActionScheduler_LogEntry( $action_id, 'action created' );
		$this->assertCount( 1, $logs );
		$this->assertEquals( $expected->get_action_id(), $logs[0]->get_action_id() );
		$this->assertEquals( $expected->get_message(), $logs[0]->get_message() );
	}

	public function test_execution_logs() {
		$action_id = as_schedule_single_action( time(), __METHOD__ );
		$logger = ActionScheduler::logger();
		$started = new ActionScheduler_LogEntry( $action_id, 'action started via Unit Tests' );
		$finished = new ActionScheduler_LogEntry( $action_id, 'action complete via Unit Tests' );

		$runner = ActionScheduler_Mocker::get_queue_runner();
		$runner->run( 'Unit Tests' );

		// Expect 3 logs with the correct action ID.
		$logs = $logger->get_logs( $action_id );
		$this->assertCount( 3, $logs );
		foreach ( $logs as $log ) {
			$this->assertEquals( $action_id, $log->get_action_id() );
		}

		// Expect created, then started, then completed.
		$this->assertEquals( 'action created', $logs[0]->get_message() );
		$this->assertEquals( $started->get_message(), $logs[1]->get_message() );
		$this->assertEquals( $finished->get_message(), $logs[2]->get_message() );
	}

	public function test_failed_execution_logs() {
		$hook = __METHOD__;
		add_action( $hook, array( $this, '_a_hook_callback_that_throws_an_exception' ) );
		$action_id = as_schedule_single_action( time(), $hook );
		$logger = ActionScheduler::logger();
		$started = new ActionScheduler_LogEntry( $action_id, 'action started via Unit Tests' );
		$finished = new ActionScheduler_LogEntry( $action_id, 'action complete via Unit Tests' );
		$failed = new ActionScheduler_LogEntry( $action_id, 'action failed via Unit Tests: Execution failed' );

		$runner = ActionScheduler_Mocker::get_queue_runner();
		$runner->run( 'Unit Tests' );

		// Expect 3 logs with the correct action ID.
		$logs = $logger->get_logs( $action_id );
		$this->assertCount( 3, $logs );
		foreach ( $logs as $log ) {
			$this->assertEquals( $action_id, $log->get_action_id() );
			$this->assertNotEquals( $finished->get_message(), $log->get_message() );
		}

		// Expect created, then started, then failed.
		$this->assertEquals( 'action created', $logs[0]->get_message() );
		$this->assertEquals( $started->get_message(), $logs[1]->get_message() );
		$this->assertEquals( $failed->get_message(), $logs[2]->get_message() );
	}

	public function test_fatal_error_log() {
		$action_id = as_schedule_single_action( time(), __METHOD__ );
		$logger = ActionScheduler::logger();
		do_action( 'action_scheduler_unexpected_shutdown', $action_id, array(
			'type' => E_ERROR,
			'message' => 'Test error',
			'file' => __FILE__,
			'line' => __LINE__,
		));

		$logs = $logger->get_logs( $action_id );
		$found_log = FALSE;
		foreach ( $logs as $l ) {
			if ( strpos( $l->get_message(), 'unexpected shutdown' ) === 0 ) {
				$found_log = TRUE;
			}
		}
		$this->assertTrue( $found_log, 'Unexpected shutdown log not found' );
	}

	public function test_canceled_action_log() {
		$action_id = as_schedule_single_action( time(), __METHOD__ );
		as_unschedule_action( __METHOD__ );
		$logger = ActionScheduler::logger();
		$logs = $logger->get_logs( $action_id );
		$expected = new ActionScheduler_LogEntry( $action_id, 'action canceled' );
		$this->assertEquals( $expected->get_message(), end( $logs )->get_message() );
	}

	public function test_deleted_action_cleanup() {
		$time = as_get_datetime_object('-10 minutes');
		$schedule = new \ActionScheduler_SimpleSchedule($time);
		$action = new \ActionScheduler_Action('my_hook', array(), $schedule);
		$store = new ActionScheduler_DBStore();
		$action_id = $store->save_action($action);

		$logger = new ActionScheduler_DBLogger();
		$logs = $logger->get_logs( $action_id );
		$this->assertNotEmpty( $logs );

		$store->delete_action( $action_id );
		$logs = $logger->get_logs( $action_id );
		$this->assertEmpty( $logs );
	}

	public function _a_hook_callback_that_throws_an_exception() {
		throw new \RuntimeException('Execution failed');
	}
}
