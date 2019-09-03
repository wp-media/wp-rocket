<?php

/**
 * Class ActionScheduler_Lock_Test
 * @package test_cases\lock
 */
class ActionScheduler_OptionLock_Test extends ActionScheduler_UnitTestCase {
	public function test_instance() {
		$lock = ActionScheduler::lock();
		$this->assertInstanceOf( 'ActionScheduler_Lock', $lock );
		$this->assertInstanceOf( 'ActionScheduler_OptionLock', $lock );
	}

	public function test_is_locked() {
		$lock      = ActionScheduler::lock();
		$lock_type = md5( rand() );

		$this->assertFalse( $lock->is_locked( $lock_type ) );

		$lock->set( $lock_type );
		$this->assertTrue( $lock->is_locked( $lock_type ) );
	}

	public function test_set() {
		$lock      = ActionScheduler::lock();
		$lock_type = md5( rand() );

		$lock->set( $lock_type );
		$this->assertTrue( $lock->is_locked( $lock_type ) );
	}

	public function test_get_expiration() {
		$lock      = ActionScheduler::lock();
		$lock_type = md5( rand() );

		$lock->set( $lock_type );

		$expiration   = $lock->get_expiration( $lock_type );
		$current_time = time();

		$this->assertGreaterThanOrEqual( 0, $expiration );
		$this->assertGreaterThan( $current_time, $expiration );
		$this->assertLessThan( $current_time + MINUTE_IN_SECONDS + 1, $expiration );
	}
}
