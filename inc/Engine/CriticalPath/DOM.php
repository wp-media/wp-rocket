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
}
