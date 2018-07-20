<?php

/**
 * @group timezone
 */
class ActionScheduler_TimezoneHelper_Test extends ActionScheduler_UnitTestCase {

	/**
	 * Ensure that the timezone string we expect works properly.
	 *
	 * @dataProvider local_timezone_provider
	 *
	 * @param $timezone_string
	 */
	public function test_local_timezone_strings( $timezone_string ) {
		$timezone_filter = function ( $tz ) use ( $timezone_string ) {
			return $timezone_string;
		};

		add_filter( 'option_timezone_string', $timezone_filter );
		$timezone = ActionScheduler_TimezoneHelper::get_local_timezone( true );
		$this->assertInstanceOf( 'DateTimeZone', $timezone );
		$this->assertEquals( $timezone_string, $timezone->getName() );
		remove_filter( 'option_timezone_string', $timezone_filter );
	}

	public function local_timezone_provider() {
		return array(
			array( 'America/New_York' ),
			array( 'Australia/Melbourne' ),
			array( 'UTC' ),
		);
	}

	/**
	 * Ensure that most GMT offsets don't return UTC as the timezone.
	 *
	 * @dataProvider local_timezone_offsets_provider
	 *
	 * @param $gmt_offset
	 */
	public function test_local_timezone_offsets( $gmt_offset ) {
		$gmt_filter = function ( $gmt ) use ( $gmt_offset ) {
			return $gmt_offset;
		};

		add_filter( 'option_gmt_offset', $gmt_filter );
		try {
			$timezone = ActionScheduler_TimezoneHelper::get_local_timezone( true );
		} catch ( Exception $_e ) {
			$e = $_e;
			// Handle outside this block...
		}
		remove_filter( 'option_gmt_offset', $gmt_filter );

		if ( isset( $e ) ) {
			if ( false !== stripos( $e->getMessage(), 'unknown or bad timezone' ) ) {
				$this->fail( sprintf( 'GMT offset [%s] caused fatal error.', $gmt_offset ) );
			} else {
				throw $e;
			}
		}

		$this->assertInstanceOf( 'DateTimeZone', $timezone );
		$this->assertNotEquals(
			'UTC',
			$timezone->getName(),
			sprintf( 'GMT offset [%s] transformed into UTC', $gmt_offset )
		);
	}

	public function local_timezone_offsets_provider() {
		return array(
			array( '-11' ),
			array( '-10.5' ),
			array( '-10' ),
			array( '-9' ),
			array( '-8' ),
			array( '-7' ),
			array( '-6' ),
			array( '-5' ),
			array( '-4.5' ),
			array( '-4' ),
			array( '-3.5' ),
			array( '-3' ),
			array( '-2' ),
			array( '-1' ),
			array( '1' ),
			array( '1.5' ),
			array( '2' ),
			array( '3' ),
			array( '4' ),
			array( '5' ),
			array( '5.5' ),
			array( '5.75' ),
			array( '6' ),
			array( '7' ),
			array( '8' ),
			array( '8.5' ),
			array( '9' ),
			array( '9.5' ),
			array( '10' ),
			array( '10.5' ),
			array( '11' ),
			array( '11.5' ),
			array( '12' ),
			array( '13' ),
		);
	}

	/**
	 * There are certain GMT offsets that we expect to return UTC as the timezone.
	 *
	 * @dataProvider local_timezone_offsets_utc_provider
	 *
	 * @param string $gmt_offset
	 * @param string $possible_string
	 */
	public function test_local_timezone_offsets_utc( $gmt_offset, $possible_string ) {
		$gmt_filter = function ( $gmt ) use ( $gmt_offset ) {
			return $gmt_offset;
		};

		add_filter( 'option_gmt_offset', $gmt_filter );
		try {
			$timezone = ActionScheduler_TimezoneHelper::get_local_timezone( true );
		} catch ( Exception $_e ) {
			$e = $_e;
			// Handle outside this block...
		}
		remove_filter( 'option_gmt_offset', $gmt_filter );

		if ( isset( $e ) ) {
			if ( false !== stripos( $e->getMessage(), 'unknown or bad timezone' ) ) {
				$this->fail( sprintf( 'GMT offset [%s] caused fatal error.', $gmt_offset ) );
			} else {
				throw $e;
			}
		}

		$this->assertInstanceOf( 'DateTimeZone', $timezone );

		/*
		 * PHP versions less that 7 should have a recognized value for some of these offsets.
		 * This means that we expect a different result for older versions of PHP than we do
		 * for PHP 7+
		 */
		if ( ! empty( $possible_string) && version_compare( phpversion(), '7.0.0', '<' ) ) {
			$this->assertEquals(
				$possible_string,
				$timezone->getName(),
				sprintf( 'GMT offset [%s] should be transformed to %s string.', $gmt_offset, $possible_string )
			);
		} else {
			$this->assertEquals(
				'UTC',
				$timezone->getName(),
				sprintf( 'GMT offset [%s] transformed into UTC', $gmt_offset )
			);
		}
	}

	public function local_timezone_offsets_utc_provider() {
		return array(
			array( '-12', 'Pacific/Kwajalein' ),
			array( '-11.5', 'Pacific/Niue' ),
			array( '-9.5', 'Pacific/Marquesas' ),
			array( '-8.5', 'Pacific/Pitcairn' ),
			array( '-7.5', '' ),
			array( '-6.5', '' ),
			array( '-5.5', '' ),
			array( '-2.5', '' ),
			array( '-1.5', '' ),
			array( '-0.5', '' ),
			array( '0.5', '' ),
			array( '2.5', 'Africa/Mogadishu' ),
			array( '3.5', 'Asia/Tehran' ),
			array( '4.5', 'Asia/Kabul' ),
			array( '6.5', 'Asia/Kolkata' ),
			array( '7.5', 'Asia/Brunei' ),
			array( '8.75', 'Australia/Eucla' ),
			array( '12.75', 'Pacific/Chatham' ),
			array( '13.75', '' ),
			array( '14', 'Pacific/Kiritimati' ),
		);
	}
}
