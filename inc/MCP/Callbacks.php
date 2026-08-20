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
}
