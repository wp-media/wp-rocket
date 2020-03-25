<?php

namespace WP_Rocket\Tests;

trait ArrayTrait {

	/**
	 * Flatten a multi-dimensional associative array with the delimiter.
	 *
	 * @param array  $array     Array to flatten.
	 * @param string $prepend   Optional. String to prepend to key.
	 * @param bool   $arrayOnly Optional. When true, only processes when value is an array.
	 *
	 * @return array flattened array
	 */
	public static function flatten( array $array, $prepend = '', $arrayOnly = false, $delimiter '/' ) {
		$results = [];

		foreach ( $array as $key => $value ) {
			if ( $arrayOnly ) {
				if ( ! is_array( $value ) ) {
					continue;
				}
				$results[ "{$prepend}{$key}" ] = $value;
			}
			if ( ( $arrayOnly || is_array( $value ) ) && ! empty( $value ) ) {
				$results = array_merge( 
					$results,
					static::flatten( $value, "{$prepend}{$key}{$delimiter}", $arrayOnly, $delimiter ) 
				);
			} elseif ( ! $arrayOnly ) {
				$results[ "{$prepend}{$key}" ] = $value;
			}
		}

		return $results;
	}

	/**
	 * Get an item from an array using delimiter notation.
	 *
	 * @param array  $search    Search array.
	 * @param string $keyToFind Key to find.
	 * @param mixed  $default   Optional. Default value to return if key does not exist in search array.
	 * @param string $delimiter Optional. The keys' delimiter, i.e. what separating the keys.
	 *
	 * @return mixed value returned.
	 */
	public static function get( $search, $keyToFind, $default = null, $delimiter = '/' ) {
		if ( ! is_array( $search ) ) {
			return $default;
		}

		if ( is_null( $keyToFind ) ) {
			return $search;
		}

		if ( array_key_exists( $keyToFind, $search ) ) {
			return $search[ $keyToFind ];
		}

		foreach ( explode( $delimiter, $keyToFind ) as $segment ) {
			if ( is_array( $search ) && array_key_exists( $segment, $search ) ) {
				$search = $search[ $segment ];
			} else {
				return $default;
			}
		}

		return $search;
	}

	/**
	 * Check if an item exists in an array using delimiter notation.
	 *
	 * @param array  $search    Search array.
	 * @param string $keyToFind Key to find.
	 * @param string $delimiter Optional. The keys' delimiter, i.e. what separates the keys.
	 *
	 * @return bool
	 */
	public static function has( array $search, $keyToFind, $delimiter = '/' ) {
		if ( empty( $search ) ) {
			return false;
		}

		if ( is_null( $keyToFind ) ) {
			return false;
		}

		if ( array_key_exists( $keyToFind, $search ) ) {
			return true;
		}

		foreach ( explode( $delimiter, $keyToFind ) as $segment ) {
			if ( array_key_exists( $segment, $search ) ) {
				$search = $search[ $segment ];
			} else {
				return false;
			}
		}

		return true;
	}
}
