<?php

namespace WP_Rocket\Engine\CriticalPath;

use DOMElement;

class AsyncCSS extends DOM {

	/**
	 * Array of URLs to exclude.
	 *
	 * @var array
	 */
	private $css_urls_to_exclude;

	/**
	 * The "onload" attribute defaults.
	 *
	 * @var array
	 */
	protected $onload_defaults = [
		'this.onload' => 'null',
		'this.media'  => "'all'",
		'this.rel'    => "'stylesheet'",
	];

	/**
	 * Modifies the given HTML for async CSS, i.e. defer loading of CSS files.
	 *
	 * @since  3.6.2
	 *
	 * @param string $html HTML code.
	 *
	 * @return string Updated HTML code.
	 */
	public function modify_html( $html ) {
		$css_links = $this->dom->query( $this->get_query() );

		if ( empty( $css_links ) || $css_links->length === 0 ) {
			$this->dom = null;

			return $html;
		}

		foreach ( $css_links as $css ) {
			$this->modify_css( $css );
		}

		$html = $this->dom->get_html();

		// Reset.
		$this->reset();

		return $html;
	}

	/**
	 * Resets state.
	 *
	 * @since 3.6.2
	 */
	protected function reset() {
		parent::reset();
		$this->css_urls_to_exclude = [];
	}

	/**
	 * Checks if we should apply deferring of CSS files.
	 *
	 * @return bool True if we should, false otherwise.
	 */
	protected function okay_to_create_dom() {
		if (
			rocket_get_constant( 'DONOTROCKETOPTIMIZE' )
			||
			rocket_get_constant( 'DONOTASYNCCSS' )
		) {
			return false;
		}

		if ( ! (bool) $this->options->get( 'async_css', 0 ) ) {
			return false;
		}

		if (
			empty( $this->critical_css->get_current_page_critical_css() )
			&&
			empty( $this->options->get( 'critical_css', '' ) )
		) {
			return false;
		}

		return ! is_rocket_post_excluded_option( 'async_css' );
	}

	/**
	 * Get the XPath query string.
	 *
	 * @since 3.6.2
	 *
	 * @return string
	 */
	protected function get_query() {
		$exclude = $this->get_css_to_exclude();
		if ( '' === $exclude ) {
			return '//link[@type="text/css"]';
		}

		return '//link[@type="text/css" and ' . $exclude . ']';
	}

	/**
	 * Gets the CSS URLs to exclude from async CSS.
	 *
	 * @since 3.6.2
	 *
	 * @return string
	 */
	protected function get_css_to_exclude() {
		$hrefs = $this->critical_css->get_exclude_async_css();
		if ( empty( $hrefs ) ) {
			return '';
		}

		$query = [];
		foreach ( $hrefs as $href ) {
			$query[] = sprintf( 'contains(@href, "%s")', $href );
		}

		return 'not(' . implode( ' or ', $query ) . ')';
	}

	/**
	 * Modifies the CSS <link> node for async css.
	 *
	 * @since 3.6.2
	 *
	 * @param DOMElement $css CSS <link> DOMElement.
	 */
	protected function modify_css( $css ) {
		$this->set_noscript( $css->cloneNode() );

		$css->setAttribute( 'rel', 'preload' );

		$css->setAttribute( 'as', 'style' );

		$this->build_onload( $css );

		$css->setAttribute( 'media', 'print' );
	}

	/**
	 * Builds the "onload" attribute value(s).
	 *
	 * @since 3.6.2
	 *
	 * @param DOMElement $css CSS <link> DOMElement.
	 *
	 * @return string "onload" attribute value.
	 */
	protected function build_onload( $css ) {
		$values = $css->hasAttribute( 'onload' )
			? $this->get_onload_values( $css )
			: $this->merge_default_onload_values( $css );

		$values = $this->array_to_string( $values, ';', '=' );

		$css->setAttribute( 'onload', $values );
	}

	/**
	 * Merges the default onload values with the given array of values..
	 *
	 * @since 3.6.2
	 *
	 * @param array $values Optional. Array of values to add default values to. Default: [].
	 *
	 * @return array merged array of values.
	 */
	private function merge_default_onload_values( $css, array $values = [] ) {
		foreach ( $this->onload_defaults as $key => $value ) {
			if ( array_key_exists( $key, $values ) ) {
				continue;
			}

			if ( 'this.media' === $key ) {
				$value = $this->get_onload_media( $value, $css, true );
			}

			$values[ $key ] = $this->prepare_for_value_embed( $value );
		}

		return $values;
	}

	/**
	 * Gets the "onload" attribute values.
	 *
	 * @since 3.6.2
	 *
	 * @param DOMElement $css CSS <link> DOMElement.
	 *
	 * @return array
	 */
	protected function get_onload_values( $css ) {
		$values     = [];
		$raw_values = explode( ';', $css->getAttribute( 'onload' ) );

		// Walk the raw onload values to build up an array of values.
		foreach ( $raw_values as $value ) {
			$value = trim( $value );
			if ( empty( $value ) ) {
				continue;
			}

			if ( ! $this->string_contains( $value, '=' ) ) {
				$values[] = $this->replace_double_quotes( $value );
				continue;
			}

			list( $key, $value ) = explode( '=', $value );

			if ( is_string( $value ) ) {
				$value = trim( $value );
			}

			$key = trim( $key );

			switch ( $key ) {
				case 'this.onload':
				case 'onload':
					$values['this.onload'] = $value;
					break;
				case 'this.media':
				case 'media':
					$values['this.media'] = $this->prepare_for_value_embed( $this->get_onload_media( $value, $css ) );
					break;
				case 'this.rel':
				case 'rel':
					$values['this.rel'] = $this->prepare_for_value_embed( $this->onload_defaults['this.rel'] );
					break;
				default:
					if ( ! $this->string_starts_with( $key, 'this.' ) ) {
						$key = "this.{$key}";
					}
					$values[ $key ] = $this->prepare_for_value_embed( $value );
			}
		}

		// Check that each default exists. If no, add it.
		// Using foreach to respect the original order of the onload values.
		return $this->merge_default_onload_values( $css, $values );
	}

	/**
	 * Gets the onload media value.
	 *
	 * @since 3.6.2
	 *
	 * @param string     $value            Existing onload media value.
	 * @param DOMElement $css              CSS <link> DOMElement.
	 * @param bool       $check_media_attr Optional. When true, force check for the media attribute.
	 *
	 * @return string
	 */
	private function get_onload_media( $value, $css, $check_media_attr = false ) {
		if ( $check_media_attr || ( empty( $value ) && ! $this->is_null( $value ) ) ) {

			if ( ! $css->hasAttribute( 'media' ) ) {
				return $this->onload_defaults['this.media'];
			}

			$value = $css->getAttribute( 'media' );
		}

		if ( in_array( $value, [ 'print', "'print'", '"print"' ], true ) ) {
			return $this->onload_defaults['this.media'];
		}

		return $value;
	}
}
