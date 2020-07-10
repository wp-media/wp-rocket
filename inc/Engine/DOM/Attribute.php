<?php

namespace WP_Rocket\Engine\DOM;

/**
 * Helpers to make it easier to work with attributes.
 */
class Attribute {

	/**
	 * Converts an array into a string.
	 *
	 * @since 3.6.2
	 *
	 * @param array  $array    The array to convert.
	 * @param string $glue     Glue between the string parts.
	 * @param string $operator Operator between the key and value when flatten the array.
	 *
	 * @return string converted string.
	 */
	public static function array_to_string( array $array, $glue, $operator ) {
		return implode( $glue, self::flatten( $array, $operator ) );
	}

	/**
	 * Checks if the string contains the given needle.
	 *
	 * @since 3.6.2
	 *
	 * @param string $search_string Search string.
	 * @param string $needle        Needle to find.
	 *
	 * @return bool
	 */
	public static function contains( $search_string, $needle ) {
		return ( false !== strpos( $search_string, $needle ) );
	}

	/**
	 * Flattens an array from key => value to string elements.
	 *
	 * For index key, the value is stored as the element.
	 * For keys, the key is combined with the value with the operator as the separator.
	 *
	 * @since 3.6.2
	 *
	 * @param array  $array    The array to flatten.
	 * @param string $operator The separator between the key and value.
	 *
	 * @return array
	 */
	public static function flatten( array $array, $operator ) {
		$flat = [];

		foreach ( $array as $key => $value ) {
			if ( is_integer( $key ) ) {
				$flat[] = $value;
			} else {
				$flat[] = "{$key}{$operator}{$value}";
			}
		}

		return $flat;
	}

	/**
	 * Checks if the given element has a href attribute and it's not empty.
	 *
	 * @since 3.6.2
	 *
	 * @param DOMElement $element The element DOMElement.
	 *
	 * @return bool true when href exists and not empty; else false.
	 */
	public static function has_href( $element ) {
		if ( ! $element->hasAttribute( 'href' ) ) {
			return false;
		}

		$href = $element->getAttribute( 'href' );

		return ! empty( trim( $href ) );
	}

	/**
	 * Checks if the given value is set to NULL, 'null', "null", or null.
	 *
	 * @since 3.6.2
	 *
	 * @param string $value Value to check.
	 *
	 * @return bool
	 */
	public static function is_null( $value ) {
		return (
			is_null( $value )
			||
			'null' === $value
			||
			"null" === $value // phpcs:ignore Squiz.Strings.DoubleQuoteUsage.NotRequired -- This is intentional.
		);
	}

	/**
	 * Prepares value for embedding into the attribute:
	 *    1. Wraps strings in quotes.
	 *    2. Replaces double quotes with single quotes.
	 *
	 * @since 3.6.2
	 *
	 * @param string $value Attribute value.
	 *
	 * @return string prepared attribute value.
	 */
	public static function prepare_for_embed( $value ) {
		if ( self::is_null( $value ) ) {
			return 'null';
		}

		if ( empty( $value ) ) {
			return '';
		}

		if ( ! is_string( $value ) ) {
			return $value;
		}

		$value = self::strip_escaped_quotes( $value );

		if ( "'" === $value[0] ) {
			return $value;
		}

		if ( '"' === $value[0] ) {
			return self::replace_double_quotes( $value );
		}

		return "'{$value}'";
	}

	/**
	 * Replaces double "" quotes with single quotes ''.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value Value to replace double quotes.
	 *
	 * @return string value with single quotes around it instead of double.
	 */
	public static function replace_double_quotes( $value ) {
		return str_replace( '"', "'", $value );
	}

	/**
	 * Checks if the search string starts with the given needle.
	 *
	 * @since 3.6.2
	 *
	 * @param string $search_string Search string.
	 * @param string $needle        Needle to find.
	 *
	 * @return bool
	 */
	public static function starts_with( $search_string, $needle ) {
		return ( substr( $search_string, 0, strlen( $needle ) ) === $needle );
	}

	/**
	 * Strips escaped quotes in the given string.
	 *
	 * @since 3.6.2
	 *
	 * @param string $value Given string.
	 *
	 * @return string
	 */
	private static function strip_escaped_quotes( $value ) {
		if ( strlen( $value ) < 4 ) {
			return $value;
		}

		if ( self::starts_with( $value, "\'" ) ) {
			return str_replace( "\'", "'", $value );
		}

		if ( self::starts_with( $value, '\"' ) ) {
			return str_replace( '\"', "'", $value );
		}

		return $value;
	}
}
