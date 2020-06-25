<?php

namespace WP_Rocket\Engine\CriticalPath;

use DOMNode;
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
	private $dom;

	/**
	 * Array of URLs to exclude.
	 *
	 * @var array
	 */
	private $css_urls_to_exclude;

	/**
	 * Creates an instance of the CriticalPath DOM Handler.
	 *
	 * @param CriticalCSS  $critical_css Critical CSS instance.
	 * @param Options_Data $options      WP Rocket options.
	 */
	public function __construct( CriticalCSS $critical_css, Options_Data $options ) {
		$this->critical_css = $critical_css;
		$this->options      = $options;
	}

	/**
	 * Modifies the given HTML for async CSS, i.e. defer loading of CSS files.
	 *
	 * @since  2.10
	 *
	 * @param string $html HTML code.
	 *
	 * @return string Updated HTML code
	 */
	public function modify_html_for_async_css( $html ) {
		if ( ! $this->maybe_async_css() ) {
			return $html;
		}

		$this->dom = HTMLDocument::from_html( $html );
		$css_links = $this->dom->get_all_css_links();

		if ( empty( $css_links ) ) {
			$this->dom = null;

			return $html;
		}

		$this->get_css_to_exclude();
		array_walk( $css_links, [ $this, 'modify_css_for_async' ] );

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
	private function maybe_async_css() {
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
	private function get_css_to_exclude() {
		$this->css_urls_to_exclude = array_flip( $this->critical_css->get_exclude_async_css() );
	}

	private function exclude_css_node( $node ) {
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
	private function modify_css_for_async( $css ) {
		if ( $this->exclude_css_node( $css ) ) {
			return;
		}

		$this->set_noscript( $css );

		$css->setAttribute( 'as', 'style' );

		$this->set_rel_stylesheet( $css );

		$this->set_onload( $css );

		$css->setAttribute( 'media', 'print' );
	}

	/**
	 * Sets the <noscript>.
	 *
	 * @since 3.6.2
	 *
	 * @param DOMElement $css The CSS <link> DOMElement.
	 */
	private function set_noscript( $css ) {
		$noscript = $this->dom->createElement( 'noscript' );
		$noscript->appendChild( $css );
		$this->dom->get_body()->appendChild( $noscript );
	}

	/**
	 * Sets the <rel="stylesheet"> attribute if it doesn't exist.
	 *
	 * @since 3.6.2
	 *
	 * @param DOMElement $css The CSS <link> DOMElement.
	 */
	private function set_rel_stylesheet( DOMElement $css ) {
		if ( ! $css->hasAttribute( 'rel' ) ) {
			return;
		}

		$css->setAttribute( 'rel', 'stylesheet' );
	}

	private function set_css_onload( $css ) {
		if ( ! $css->hasAttribute( 'onload' ) ) {
			$css->setAttribute( 'onload', "this.media='all'" );

			return;
		}

		$media  = $css->getAttribute( 'media' );
		$onload = $css->getAttribute( 'onload' );

		// Check if media= already exists.
		// If yes:
		//  a. Is it set to "all"? If no, set it to "all"
		//  b. If no, is it set to "print"? If yes, set it to "all".
		// Else: add this.media="all".
		// NOTES:
		//      1. Retain the other onload code.
		//      2. Watch out for inverted quotes.

	}
}
