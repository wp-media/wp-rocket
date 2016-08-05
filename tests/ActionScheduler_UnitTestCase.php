<?php

/**
 * Class ActionScheduler_UnitTestCase
 */
class ActionScheduler_UnitTestCase extends WP_UnitTestCase {

	protected $exiting_timezone;

	/**
	 * We want to run every test multiple times using a different timezone to make sure
	 * that they are unaffected by changes to PHP's timezone.
	 */
	public function run( PHPUnit_Framework_TestResult $result = NULL ){

		if ($result === NULL) {
			$result = $this->createResult();
		}

		if ( 'UTC' != ( $this->exiting_timezone = date_default_timezone_get() ) ) {
			date_default_timezone_set( 'UTC' );
			$result->run( $this );
		}

		date_default_timezone_set( 'Pacific/Fiji' ); // UTC+12
		$result->run( $this );

		date_default_timezone_set( 'Pacific/Tahiti' ); // UTC-10: it's a magical place
		$result->run( $this );

		date_default_timezone_set( $this->exiting_timezone );

		return $result;
	}
}
 