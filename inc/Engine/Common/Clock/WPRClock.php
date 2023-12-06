<?php

namespace WP_Rocket\Engine\Common\Clock;

class WPRClock implements ClockInterface {

	/**
	 * Retrieves the current time based on specified type.
	 *
	 *  - The 'mysql' type will return the time in the format for MySQL DATETIME field.
	 *  - The 'timestamp' or 'U' types will return the current timestamp or a sum of timestamp
	 *    and timezone offset, depending on `$gmt`.
	 *  - Other strings will be interpreted as PHP date formats (e.g. 'Y-m-d').
	 *
	 * If `$gmt` is a truthy value then both types will use GMT time, otherwise the
	 * output is adjusted with the GMT offset for the site.
	 *
	 * @since 1.0.0
	 * @since 5.3.0 Now returns an integer if `$type` is 'U'. Previously a string was returned.
	 *
	 * @param string   $type Type of time to retrieve. Accepts 'mysql', 'timestamp', 'U',
	 *                       or PHP date format string (e.g. 'Y-m-d').
	 * @param int|bool $gmt  Optional. Whether to use GMT timezone. Default false.
	 *
	 * @return int|string Integer if `$type` is 'timestamp' or 'U', string otherwise.
	 */
	public function current_time( string $type, $gmt = 0 ) {
		$current_time = current_time( $type, $gmt );
		$output       = apply_filters( 'rocket_current_time', $current_time );
		if ( ( is_string( $current_time ) && strtotime( $current_time ) ) || ( is_int( $current_time ) && $current_time >= 0 ) ) {
			return $output;
		}
		return $current_time;
	}
}
