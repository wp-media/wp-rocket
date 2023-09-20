<?php
/**
 * Base Custom Database Table Compare Query Class.
 *
 * @package     Database
 * @subpackage  Compare
 * @copyright   Copyright (c) 2021
 * @license     https://opensource.org/licenses/MIT MIT
 * @since       1.0.0
 */
namespace WP_Rocket\Dependencies\Database\Queries;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class used for generating SQL for compare clauses.
 *
 * This class is used to generate the SQL when a `compare` argument is passed to
 * the `Base` query class. It extends `Meta` so the `compare` key accepts
 * the same parameters as the ones passed to `Meta`.
 *
 * @since 1.0.0
 */
class Compare extends Meta {

	// All supported SQL comparisons
	const ALL_COMPARES = array(
		'=',
		'!=',
		'>',
		'>=',
		'<',
		'<=',
		'LIKE',
		'NOT LIKE',
		'IN',
		'NOT IN',
		'BETWEEN',
		'NOT BETWEEN',
		'EXISTS',
		'NOT EXISTS',
		'REGEXP',
		'NOT REGEXP',
		'RLIKE',
	);

	// IN and BETWEEN
	const IN_BETWEEN_COMPARES = array(
		'IN',
		'NOT IN',
		'BETWEEN',
		'NOT BETWEEN'
	);

	/**
	 * Generate SQL WHERE clauses for a first-order query clause.
	 *
	 * "First-order" means that it's an array with a 'key' or 'value'.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $clause       Query clause (passed by reference).
	 * @param array  $parent_query Parent query array.
	 * @param string $clause_key   Optional. The array key used to name the clause in the original `$meta_query`
	 *                             parameters. If not provided, a key will be generated automatically.
	 * @return array {
	 *     Array containing WHERE SQL clauses to append to a first-order query.
	 *
	 *     @type string $where SQL fragment to append to the main WHERE clause.
	 * }
	 */
	public function get_sql_for_clause( &$clause, $parent_query, $clause_key = '' ) {
		global $wpdb;

		// Default chunks
		$sql_chunks = array(
			'where' => array(),
			'join'  => array(),
		);

		// Maybe format compare clause
		if ( isset( $clause['compare'] ) ) {
			$clause['compare'] = strtoupper( $clause['compare'] );

		// Or set compare clause based on value
		} else {
			$clause['compare'] = isset( $clause['value'] ) && is_array( $clause['value'] )
				? 'IN'
				: '=';
		}

		// Fallback to equals
		if ( ! in_array( $clause['compare'], self::ALL_COMPARES, true ) ) {
			$clause['compare'] = '=';
		}

		// Uppercase or equals
		if ( isset( $clause['compare_key'] ) && ( 'LIKE' === strtoupper( $clause['compare_key'] ) ) ) {
			$clause['compare_key'] = strtoupper( $clause['compare_key'] );
		} else {
			$clause['compare_key'] = '=';
		}

		// Get comparison from clause
		$compare = $clause['compare'];

		/** Build the WHERE clause ********************************************/

		// Column name and value.
		if ( array_key_exists( 'key', $clause ) && array_key_exists( 'value', $clause ) ) {
			$column = sanitize_key( $clause['key'] );
			$value  = $clause['value'];

			// IN or BETWEEN
			if ( in_array( $compare, self::IN_BETWEEN_COMPARES, true ) ) {
				if ( ! is_array( $value ) ) {
					$value = preg_split( '/[,\s]+/', $value );
				}

			// Anything else
			} else {
				$value = trim( $value );
			}

			// Format WHERE from compare value(s)
			switch ( $compare ) {
				case 'IN':
				case 'NOT IN':
					$compare_string = '(' . substr( str_repeat( ',%s', count( $value ) ), 1 ) . ')';
					$where          = $wpdb->prepare( $compare_string, $value );
					break;

				case 'BETWEEN':
				case 'NOT BETWEEN':
					$value = array_slice( $value, 0, 2 );
					$where = $wpdb->prepare( '%s AND %s', $value );
					break;

				case 'LIKE':
				case 'NOT LIKE':
					$value = '%' . $wpdb->esc_like( $value ) . '%';
					$where = $wpdb->prepare( '%s', $value );
					break;

				// EXISTS with a value is interpreted as '='.
				case 'EXISTS':
					$compare = '=';
					$where   = $wpdb->prepare( '%s', $value );
					break;

				// 'value' is ignored for NOT EXISTS.
				case 'NOT EXISTS':
					$where = '';
					break;

				default:
					$where = $wpdb->prepare( '%s', $value );
					break;

			}

			// Maybe add column, compare, & where to chunks
			if ( ! empty( $where ) ) {
				$sql_chunks['where'][] = "{$column} {$compare} {$where}";
			}
		}

		/*
		 * Multiple WHERE clauses (for meta_key and meta_value) should
		 * be joined in parentheses.
		 */
		if ( 1 < count( $sql_chunks['where'] ) ) {
			$sql_chunks['where'] = array( '( ' . implode( ' AND ', $sql_chunks['where'] ) . ' )' );
		}

		// Return
		return $sql_chunks;
	}
}