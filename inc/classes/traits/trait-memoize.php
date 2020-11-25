<?php
namespace WP_Rocket\Traits;

/**
 * Statically store values.
 *
 * @since  3.3
 */
trait Memoize {

	/**
	 * Store the values.
	 *
	 * @var    array
	 * @since  3.3
	 */
	private static $memoized = [];

	/**
	 * Tell if a value is memoized.
	 *
	 * @since  3.3
	 *
	 * @param  string $method Name of the method.
	 * @param  array  $args   Arguments passed to the parent method. It is used to build a hash.
	 * @return bool
	 */
	final public static function is_memoized( $method, $args = [] ) {
		$hash = self::get_memoize_args_hash( $args );
		return isset( self::$memoized[ $method ][ $hash ] );
	}

	/**
	 * Get a stored value.
	 *
	 * @since  3.3
	 *
	 * @param  string $method Name of the method.
	 * @param  array  $args   Arguments passed to the parent method. It is used to build a hash.
	 * @return mixed
	 */
	final public static function get_memoized( $method, $args = [] ) {
		$hash = self::get_memoize_args_hash( $args );
		return isset( self::$memoized[ $method ][ $hash ] ) ? self::$memoized[ $method ][ $hash ] : null;
	}

	/**
	 * Cache a value.
	 *
	 * @since  3.3
	 *
	 * @param  string $method Name of the method.
	 * @param  array  $args   Arguments passed to the parent method. It is used to build a hash.
	 * @param  mixed  $value  Value to store.
	 * @return mixed          The stored value.
	 */
	final public static function memoize( $method, $args = [], $value = null ) {
		$hash = self::get_memoize_args_hash( $args );

		if ( ! isset( self::$memoized[ $method ] ) ) {
			self::$memoized[ $method ] = [];
		}

		self::$memoized[ $method ][ $hash ] = $value;
		return self::$memoized[ $method ][ $hash ];
	}

	/**
	 * Create a hash based on an array of arguments.
	 *
	 * @since  3.3
	 *
	 * @param  array $args An array of arguments.
	 * @return string
	 */
	private static function get_memoize_args_hash( $args ) {
		if ( [] === $args ) {
			return 'd751713988987e9331980363e24189ce'; // `md5( json_encode( [] ) )`
		}

		return md5( call_user_func( 'json_encode', $args ) );
	}
}
