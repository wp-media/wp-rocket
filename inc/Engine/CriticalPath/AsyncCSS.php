<?php

namespace WP_Rocket\Engine\CriticalPath;

use DOMElement;
use WP_Rocket\Engine\DOM\HTMLDocument;

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
		'this.media'  => 'all',
		'this.rel'    => 'stylesheet',
	];

	/**
	 * Modifies the given HTML for async CSS, i.e. defer loading of CSS files.
	 *
	 * @since  2.10
	 *
	 * @param string $html HTML code.
	 *
	 * @return string Updated HTML code.
	 */
	public function modify_html( $html ) {
		$css_links = $this->dom->get_all_css_links();

		if ( empty( $css_links ) ) {
			$this->dom = null;

			return $html;
		}

		$this->get_css_to_exclude();
		array_walk( $css_links, [ $this, 'modify_css' ] );

		$html = $this->dom->get_html();

		// Reset.
		$this->css_urls_to_exclude = [];
		$this->dom                 = null;

		return $html;
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
	 * Gets the CSS URLs to exclude from async CSS.
	 *
	 * @since 3.6.2
	 */
	protected function get_css_to_exclude() {
		$this->css_urls_to_exclude = array_flip( $this->critical_css->get_exclude_async_css() );
	}

	protected function exclude_css_node( $node ) {
		if ( empty( $this->css_urls_to_exclude ) ) {
			return false;
		}

		return in_array( $node->getAttribute( 'href' ), $this->css_urls_to_exclude, true );
	}

	/**
	 * Modifies the CSS <link> node for async css.
	 *
	 * @since 3.6.2
	 *
	 * @param DOMElement $css CSS <link> DOMElement.
	 */
	protected function modify_css( $css ) {
		if ( $this->exclude_css_node( $css ) ) {
			return;
		}

		$this->set_noscript( $css );

		$css->setAttribute( 'as', 'style' );

		$css->setAttribute( 'rel', 'preload' );

		$css->setAttribute( 'onload', $this->build_onload( $css ) );

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
		$this->onload_defaults['this.media'] = $css->hasAttribute( 'media' ) ? $css->getAttribute( 'media' ) : 'all';

		if ( ! $css->hasAttribute( 'onload' ) ) {
			return $this->array_to_string( $this->onload_defaults, ';', '=' );
		}

		$values = $this->get_onload_values( $css );

		$css->setAttribute( 'onload', $this->array_to_string( $values, ';', '=' ) );
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

			if ( $this->string_contains( $value, '=' ) ) {
				$values[] = $value;
				continue;
			}

			list( $key, $value ) = explode( '=', $value );

			$key   = trim( $key );
			$value = trim( $value );
			switch ( $key ) {
				case 'this.onload':
				case 'onload':
					$values['this.onload'] = $value;
					break;
				case 'this.media':
				case 'media':
					$values['this.media'] = 'print' !== $value ? $value : $this->default['this.media'];
					break;
				case 'this.rel':
				case 'rel':
					$values['this.rel'] = $this->onload_defaults['this.rel'];
					break;
				default:
					$values["this.{$key}"] = $value;
			}
		}

		// Check that each default exists. If no, add it.
		// Using foreach to respect the original order of the onload values.
		foreach ( $this->onload_defaults as $key => $value ) {
			if ( ! array_key_exists( $key, $values ) ) {
				continue;
			}

			$values[ $key ] = $value;
		}

		return $values;
	}
}
