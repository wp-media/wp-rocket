<?php
/**
 * Approved callback factories for the MCP Helpers proof of concept.
 *
 * @package WP_Rocket\MCP
 */

namespace WP_Rocket\MCP;

defined( 'ABSPATH' ) || exit;

/**
 * Factories for the WP Rocket approved callbacks.
 *
 * Each method is a factory: it receives the bound args from a stored callback
 * reference and returns the actual filter callback WordPress will run. Only ids
 * registered in the callback catalog can ever be resolved, so a stored row can
 * never introduce arbitrary code — it only supplies data these vetted factories
 * decide how to use.
 */
class Callbacks {

	/**
	 * rocket/append-to-list — append value(s) to an array-returning filter.
	 *
	 * @param array $args Bound args: { values: string[] }.
	 * @return callable
	 */
	public static function append_to_list( array $args ): callable {
		$values = array_map( 'strval', (array) ( $args['values'] ?? [] ) );

		return static function ( $list ) use ( $values ) {
			$list = is_array( $list ) ? $list : [];

			return array_values( array_unique( array_merge( $list, $values ) ) );
		};
	}

	/**
	 * rocket/remove-from-list — remove matching value(s) from an array-returning filter.
	 *
	 * @param array $args Bound args: { values: string[] }.
	 * @return callable
	 */
	public static function remove_from_list( array $args ): callable {
		$values = array_map( 'strval', (array) ( $args['values'] ?? [] ) );

		return static function ( $list ) use ( $values ) {
			$list = is_array( $list ) ? $list : [];

			return array_values( array_diff( $list, $values ) );
		};
	}

	/**
	 * rocket/return-int — return a fixed integer.
	 *
	 * @param array $args Bound args: { value: int }.
	 * @return callable
	 */
	public static function return_int( array $args ): callable {
		$value = (int) ( $args['value'] ?? 0 );

		return static function () use ( $value ) {
			return $value;
		};
	}

	/**
	 * rocket/set-array-key — set one key on an associative-array-returning filter.
	 *
	 * For filters whose value is a map (request args, ignored parameters, …) rather
	 * than a plain list. Other top-level keys are preserved. When both the new value
	 * and the existing value at the key are arrays, they are deep-merged (nested
	 * entries preserved, leaves overridden) rather than overwritten — so e.g. adding
	 * one header does not drop the others.
	 *
	 * @param array $args Bound args: { key: string, value: scalar|array (default 1) }.
	 * @return callable
	 */
	public static function set_array_key( array $args ): callable {
		$key   = (string) ( $args['key'] ?? '' );
		$value = array_key_exists( 'value', $args ) ? $args['value'] : 1;

		return static function ( $map ) use ( $key, $value ) {
			$map = is_array( $map ) ? $map : [];

			if ( '' === $key ) {
				return $map;
			}

			if ( is_array( $value ) && isset( $map[ $key ] ) && is_array( $map[ $key ] ) ) {
				$map[ $key ] = self::deep_merge( $map[ $key ], $value );
			} else {
				$map[ $key ] = $value;
			}

			return $map;
		};
	}

	/**
	 * Recursively merges $override into $base: array branches are merged, leaves
	 * (and non-array values) are overridden, and keys present only in $base are kept.
	 *
	 * @param array $base     Existing array.
	 * @param array $override Array to merge in.
	 * @return array
	 */
	private static function deep_merge( array $base, array $override ): array {
		foreach ( $override as $k => $v ) {
			if ( is_array( $v ) && isset( $base[ $k ] ) && is_array( $base[ $k ] ) ) {
				$base[ $k ] = self::deep_merge( $base[ $k ], $v );
			} else {
				$base[ $k ] = $v;
			}
		}

		return $base;
	}
}
