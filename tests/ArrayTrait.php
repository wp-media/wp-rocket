<?php

namespace WP_Rocket\Tests;

trait ArrayTrait {

	/**
	 * Flatten a multi-dimensional associative array with dots.
	 *
	 * @param array $array Array to flatten.
	 * @param string $prepend Optional. String to prepend to key.
	 * @param bool $arrayOnly Optional. When true, only processes when value is an array.
	 *
	 * @return array
	 */
	public static function flatten( array $array, $prepend = '', $arrayOnly = false ) {
		$results = [];

		foreach ( $array as $key => $value ) {
			if ( $arrayOnly ) {
				if ( ! is_array( $value ) ) {
					continue;
				}
				$results["{$prepend}{$key}"] = $value;
			}
			if ( ( $arrayOnly || is_array( $value ) ) && ! empty( $value ) ) {
				$results = array_merge( $results, self::flatten( $value, "{$prepend}{$key}/", $arrayOnly ) );
			} elseif ( ! $arrayOnly ) {
				$results["{$prepend}{$key}"] = $value;
			}
		}

		return $results;
	}

	/**
	 * Get an item from an array using "dot" notation.
	 *
	 * @param array $search Search array.
	 * @param string $keyToFind Key to find.
	 * @param mixed $default Optional. Default value to return if key does not exist in search array.
	 * @param string $delimiter Optional. The keys' delimiter, i.e. separating the keys.
	 *
	 * @return mixed value returned.
	 */
	public static function get( $search, $keyToFind, $default = null, $delimiter = '.' ) {
		if ( ! is_array( $search ) ) {
			return value( $default );
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
				return value( $default );
			}
		}

		return $search;
	}

	/**
	 * Check if an item exists in an array using "dot" notation.
	 *
	 * @param array $search Search array.
	 * @param string $keyToFind Key to find.
	 *
	 * @return bool
	 */
	public static function has( array $search, $keyToFind ) {
		if ( empty( $search ) ) {
			return false;
		}

		if ( is_null( $keyToFind ) ) {
			return false;
		}

		if ( array_key_exists( $keyToFind, $search ) ) {
			return true;
		}

		foreach ( explode( '.', $keyToFind ) as $segment ) {
			if ( static::accessible( $haystack ) && array_key_exists( $segment, $search ) ) {
				$search = $search[ $segment ];
			} else {
				return false;
			}
		}

		return true;
	}
}
