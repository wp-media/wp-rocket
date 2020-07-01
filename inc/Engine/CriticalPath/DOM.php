<?php

namespace WP_Rocket\Engine\CriticalPath;

use DOMElement;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\DOM\HTMLDocument;

class DOM {

	/**
	 * Instance of Critical CSS.
	 *
	 * @var Critical_CSS
	 */
	protected $critical_css;

	/**
	 * Instance of options.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Instance of the DOM.
	 *
	 * @var HTMLDocument
	 */
	protected $dom;

	/**
	 * <noscript> element.
	 *
	 * @var DOMElement
	 */
	protected $noscript;

	/**
	 * Creates an instance of the DOM Handler.
	 *
	 * @param CriticalCSS  $critical_css Critical CSS instance.
	 * @param Options_Data $options      WP Rocket options.
	 */
	public function __construct( CriticalCSS $critical_css, Options_Data $options ) {
		$this->critical_css = $critical_css;
		$this->options      = $options;
	}

	/**
	 * Named constructor for transforming HTML into DOM.
	 *
	 * @since 3.6.2
	 *
	 * @param CriticalCSS  $critical_css Critical CSS instance.
	 * @param Options_Data $options      WP Rocket options.
	 * @param string       $html         Optional. HTML to transform into HTML DOMDocument object.
	 *
	 * @return self Instance of this class.
	 */
	public static function from_html( CriticalCSS $critical_css, Options_Data $options, $html ) {
		$instance = new static( $critical_css, $options );

		if ( ! $instance->okay_to_create_dom() ) {
			return null;
		}

		$instance->dom = HTMLDocument::from_html( $html );

		return $instance;
	}

	/**
	 * Resets state.
	 *
	 * @since 3.6.2
	 */
	protected function reset() {
		$this->dom      = null;
		$this->noscript = null;
	}

	/**
	 * Checks if it's okay to create the DOM from the HTML. This method can be overloaded.
	 *
	 * @since 3.6.2
	 *
	 * @return bool
	 */
	protected function okay_to_create_dom() {
		return true;
	}

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
	protected function array_to_string( array $array, $glue, $operator ) {
		return implode( $glue, $this->flatten_array( $array, $operator ) );
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
	protected function flatten_array( array $array, $operator ) {
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
	 * Sets the <noscript>.
	 *
	 * @since 3.6.2
	 *
	 * @param DOMElement $element The element to append within <noscript>.
	 */
	protected function set_noscript( $element ) {
		$need_to_create = is_null( $this->noscript );

		if ( $need_to_create ) {
			$this->noscript = $this->dom->createElement( 'noscript' );
		}

		$this->noscript->appendChild( $element );

		if ( $need_to_create ) {
			$this->dom->get_body()->appendChild( $this->noscript );
		}
	}

	/**
	 * Prepares the given attribute value for embedding into the attribute.
	 *
	 * If it's a string, it's wrapped in quotations.
	 *
	 * @since 3.6.2
	 *
	 * @param mixed $value Value to wrap.
	 *
	 * @return mixed
	 */
	protected function prepare_for_value_embed( $value ) {
		if ( $this->is_null( $value ) ) {
			return 'null';
		}

		if ( empty( $value ) ) {
			return '';
		}

		if ( ! is_string( $value ) ) {
			return $value;
		}

		if ( "'" === $value[0] ) {
			return $value;
		}

		if ( '"' === $value[0] ) {
			return $this->replace_double_quotes( $value );
		}

		return "'{$value}'";
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
			"null" === $value
		);
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
	 * Checks if the string contains the given needle.
	 *
	 * @since 3.6.2
	 *
	 * @param string $search_string Search string.
	 * @param string $needle        Needle to find.
	 *
	 * @return bool
	 */
	public static function string_contains( $search_string, $needle ) {
		return ( false !== strpos( $search_string, $needle ) );
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
	protected function string_starts_with( $search_string, $needle ) {
		return ( substr( $search_string, 0, strlen( $needle ) ) === $needle );
	}
}
