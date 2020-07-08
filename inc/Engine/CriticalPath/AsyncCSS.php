<?php

namespace WP_Rocket\Engine\CriticalPath;

use DOMElement;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\DOM\HTMLDocument;
use WP_Rocket\Engine\DOM\Attribute;

class AsyncCSS {

	/**
	 * Instance of Critical CSS.
	 *
	 * @var Critical_CSS
	 */
	private $critical_css;

	/**
	 * Instance of options.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Instance of the DOM.
	 *
	 * @var HTMLDocument
	 */
	private $dom;

	/**
	 * <noscript> element.
	 *
	 * @var DOMElement
	 */
	private $noscript;

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
	private $onload_defaults = [
		'this.onload' => 'null',
		'this.media'  => "'all'",
	];

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
	 * Checks if we should apply deferring of CSS files.
	 *
	 * @return bool True if we should, false otherwise.
	 */
	private function okay_to_create_dom() {
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
	 * Modifies the given HTML for async CSS, i.e. defer loading of CSS files.
	 *
	 * @since  3.6.2
	 *
	 * @param string $html HTML code.
	 *
	 * @return string Updated HTML code.
	 */
	public function modify_html( $html ) {
		if ( is_null( $this->dom ) ) {
			return $html;
		}

		$css_links = $this->dom->query( $this->get_query() );

		if ( empty( $css_links ) || 0 === $css_links->length ) {
			$this->reset();

			return $html;
		}

		foreach ( $css_links as $css ) {
			if ( ! Attribute::has_href( $css ) ) {
				continue;
			}
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
	private function reset() {
		$this->dom                 = null;
		$this->noscript            = null;
		$this->css_urls_to_exclude = [];
	}

	/**
	 * Get the XPath query string.
	 *
	 * @since 3.6.2
	 *
	 * @return string
	 */
	private function get_query() {
		$query   = '//link[@rel="stylesheet"';
		$exclude = $this->get_css_to_exclude();
		if ( '' !== $exclude ) {
			$query .= " and {$exclude}";
		}

		return $query . ']';
	}

	/**
	 * Gets the CSS URLs to exclude from async CSS.
	 *
	 * @since 3.6.2
	 *
	 * @return string
	 */
	private function get_css_to_exclude() {
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
	private function modify_css( $css ) {
		$this->set_noscript( $css->cloneNode() );
		$this->add_preload_link( $css );

		$this->build_onload( $css );

		$css->setAttribute( 'media', 'print' );
	}

	/**
	 * Builds the "onload" attribute value(s).
	 *
	 * @since 3.6.2
	 *
	 * @param DOMElement $css CSS <link> DOMElement.
	 */
	private function build_onload( $css ) {
		$values = $css->hasAttribute( 'onload' )
			? $this->get_onload_values( $css )
			: $this->merge_default_onload_values( $css );

		$values = Attribute::array_to_string( $values, ';', '=' );

		$css->setAttribute( 'onload', $values );
	}

	/**
	 * Merges the default onload values with the given array of values..
	 *
	 * @since 3.6.2
	 *
	 * @param DOMElement $css    CSS <link> DOMElement.
	 * @param array      $values Optional. Array of values to add default values to. Default: [].
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

			$values[ $key ] = Attribute::prepare_for_embed( $value );
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
	private function get_onload_values( $css ) {
		$values     = [];
		$raw_values = explode( ';', $css->getAttribute( 'onload' ) );

		// Walk the raw onload values to build up an array of values.
		foreach ( $raw_values as $value ) {
			$value = trim( $value );
			if ( empty( $value ) ) {
				continue;
			}

			if ( ! Attribute::contains( $value, '=' ) ) {
				$values[] = Attribute::replace_double_quotes( $value );
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
					$values['this.media'] = Attribute::prepare_for_embed( $this->get_onload_media( $value, $css ) );
					break;
				default:
					if ( ! Attribute::starts_with( $key, 'this.' ) ) {
						$key = "this.{$key}";
					}
					$values[ $key ] = Attribute::prepare_for_embed( $value );
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
		if ( $check_media_attr || ( empty( $value ) && ! Attribute::is_null( $value ) ) ) {

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

	/**
	 * Sets the <noscript>.
	 *
	 * @since 3.6.2
	 *
	 * @param DOMElement $element The element to append within <noscript>.
	 */
	private function set_noscript( $element ) {
		$need_to_create = is_null( $this->noscript );

		if ( $need_to_create ) {
			$this->noscript = $this->dom->createElement( 'noscript' );
		}

		// Removes the id attribute to avoid duplicate IDs in the DOM.
		$element->removeAttribute( 'id' );

		$this->noscript->appendChild( $element );

		if ( $need_to_create ) {
			$this->dom->get_body()->appendChild( $this->noscript );
		}
	}

	/**
	 * Adds <link rel="preload" href="same css URL" as="style"> before the given stylesheet element.
	 *
	 * @since 3.6.2
	 *
	 * @param DOMElement $css CSS <link> DOMElement.
	 */
	private function add_preload_link( $css ) {
		$element = $this->dom->createElement( 'link' );
		$element->setAttribute( 'rel', 'preload' );
		$element->setAttribute( 'href', $css->getAttribute( 'href' ) );
		$element->setAttribute( 'as', 'style' );

		// Insert the new element before the stylesheet's node.
		$css->parentNode->insertBefore( $element, $css ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}
}
