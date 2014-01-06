<?php

/**
 * Class TaskScheduler_Job_Test
 * @group jobs
 */
class TaskScheduler_Job_Test extends TaskScheduler_UnitTestCase {
	public function test_set_schedule() {
		$time = new DateTime();
		$schedule = new TaskScheduler_SimpleSchedule($time);
		$job = new TaskScheduler_Job('my_hook', array(), $schedule);
		$this->assertEquals( $schedule, $job->get_schedule() );
	}

	public function test_null_schedule() {
		$job = new TaskScheduler_Job('my_hook');
		$this->assertInstanceOf( 'TaskScheduler_NullSchedule', $job->get_schedule() );
	}

	public function test_set_hook() {
		$job = new TaskScheduler_Job('my_hook');
		$this->assertEquals( 'my_hook', $job->get_hook() );
	}

	public function test_args() {
		$job = new TaskScheduler_Job('my_hook');
		$this->assertEmpty($job->get_args());

		$job = new TaskScheduler_Job('my_hook', array(5,10,15));
		$this->assertEqualSets(array(5,10,15), $job->get_args());
	}

	public function test_set_group() {
		$job = new TaskScheduler_Job('my_hook', array(), NULL, 'my_group');
		$this->assertEquals('my_group', $job->get_group());
	}

	public function test_execute() {
		$mock = new MockAction();

		$random = md5(rand());
		add_action( $random, array( $mock, 'action' ) );

		$job = new TaskScheduler_Job( $random, array($random) );
		$job->execute();

		remove_action( $random, array( $mock, 'action' ) );

		$this->assertEquals( 1, $mock->get_call_count() );
		$event = reset($mock->get_events());
		$this->assertEquals( $random, reset($event['args']) );
	}
}
 