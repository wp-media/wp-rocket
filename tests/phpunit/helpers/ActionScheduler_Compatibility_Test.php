<?php
/**
 * @group helpers
 */
class ActionScheduler_Compatibility_Test extends ActionScheduler_UnitTestCase {
	/**
	 * Test the logic relating to ActionScheduler_Compatibility::raise_time_limit().
	 */
	public function test_raise_time_limit() {
		// We'll want to restore things after this test.
		$default_max_execution_time = ini_get( 'max_execution_time' );

		ini_set( 'max_execution_time', 0 );
		ActionScheduler_Compatibility::raise_time_limit( 10 );
		$this->assertEquals(
			'0',
			ini_get( 'max_execution_time' ),
			'If the max_execution_time was already zero (unlimited), then it will not be changed.'
		);

		ini_set( 'max_execution_time', 60 );
		ActionScheduler_Compatibility::raise_time_limit( 30 );
		$this->assertEquals(
			'60',
			ini_get( 'max_execution_time' ),
			'If the max_execution_time was already a higher value than we specify, then it will not be changed.'
		);

		ActionScheduler_Compatibility::raise_time_limit( 200 );
		$this->assertEquals(
			'200',
			ini_get( 'max_execution_time' ),
			'If the max_execution_time was a lower value than we specify, but was above zero, then it will be updated to the new value.'
		);

		ActionScheduler_Compatibility::raise_time_limit( 0 );
		$this->assertEquals(
			'0',
			ini_get( 'max_execution_time' ),
			'If the max_execution_time was a positive, non-zero value and we then specify zero (unlimited) as the new value, then it will be updated.'
		);

		// Cleanup.
		ini_set( 'max_execution_time', $default_max_execution_time );
	}
}
