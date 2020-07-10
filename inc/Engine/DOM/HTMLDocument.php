<?php

namespace WP_Rocket\Engine\DOM;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXpath;

class HTMLDocument extends DOMDocument {

	/**
	 * DOMDocument default version.
	 *
	 * @var string
	 */
	const VERSION = '1.0';

	/**
	 * HTML markup default UTF-8 encoding.
	 *
	 * @var string
	 */
	const DEFAULT_ENCODING = 'UTF-8';

	/**
	 * Actual encoding.
	 *
	 * @var string
	 */
	protected $current_encoding;

	/**
	 * HTML in string format.
	 *
	 * @var string
	 */
	protected $html;

	/**
	 * <head> element.
	 *
	 * @var DOMElement
	 */
	protected $head;

	/**
	 * <body> element.
	 *
	 * @var DOMElement
	 */
	protected $body;

	/**
	 * Instance of XPath.
	 *
	 * @var DOMXpath
	 */
	protected $xpath;

	/**
	 * Creates a new HTML DOMDocument object.
	 *
	 * @link  https://php.net/manual/domdocument.construct.php
	 *
	 * @since 3.6.2
	 *
	 * @param string $version  Optional. The version number of the document as part of the XML declaration.
	 * @param string $encoding Optional. The encoding of the document as part of the XML declaration.
	 */
	public function __construct( $version = '1.0', $encoding = null ) {
		$this->init_encoding( $encoding );

		$version = (string) $version;
		if ( empty( $version ) ) {
			$version = self::VERSION;
		}

		parent::__construct( $version, $this->current_encoding );
	}

	/**
	 * Named constructor for transforming HTML into DOM.
	 *
	 * @since 3.6.2
	 *
	 * @param string $html     HTML to transform into HTML DOMDocument object.
	 * @param string $version  Optional. The version number of the document as part of the XML declaration.
	 * @param string $encoding Optional. The encoding of the document as part of the XML declaration.
	 *
	 * @return HTMLDocument|false DOM generated from provided HTML, or false if the transformation failed.
	 */
	public static function from_html( $html, $version = '', $encoding = null ) {
		if ( empty( $html ) ) {
			return false;
		}

		$dom = new self( $version, $encoding );

		$html = $dom->prepare_html( $html );

		// LIBXML_SCHEMA_CREATE valid in Libxml 2.6.14+.
		if ( ! $dom->loadHTML( $html, LIBXML_SCHEMA_CREATE ) ) {
			return false;
		}

		$dom->init_xpath();
		$dom->head = $dom->getElementsByTagName( 'head' )->item( 0 );
		$dom->body = $dom->getElementsByTagName( 'body' )->item( 0 );

		return $dom;
	}

	/**
	 * Named constructor for transforming a HTML fragment into DOM.
	 *
	 * A fragment is partial HTML. When using this constructor, <html>, <head>, and <body> will not be added by the DOM.
	 *
	 * @since 3.6.2
	 *
	 * @param string $fragment The HTML fragment to transform into HTML DOMDocument object.
	 * @param string $version  Optional. The version number of the document as part of the XML declaration.
	 * @param string $encoding Optional. The encoding of the document as part of the XML declaration.
	 *
	 * @return HTMLDocument|false DOM generated from provided HTML, or false if the transformation failed.
	 */
	public static function from_fragment( $fragment, $version = '', $encoding = null ) {
		if ( empty( $fragment ) ) {
			return false;
		}

		$dom = new self( $version, $encoding );

		// @TODO Need to check versions.
		$options = LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD;

		$fragment = $dom->prepare_html( $fragment );

		if ( ! $dom->loadHTML( $fragment, $options ) ) {
			return false;
		}

		$dom->init_xpath();

		return $dom;
	}

	/**
	 * Gets the HTML in string format.
	 *
	 * @since 3.6.2
	 *
	 * @return string
	 */
	public function get_html() {
		return mb_convert_encoding( $this->saveHTML(), $this->current_encoding, 'HTML-ENTITIES' );
	}

	/**
	 * Returns the <head> DOMElement.
	 *
	 * @since 3.6.2
	 *
	 * @return DOMElement
	 */
	public function get_head() {
		return $this->head;
	}

	/**
	 * Returns the <body> DOMElement.
	 *
	 * @since 3.6.2
	 *
	 * @return DOMElement
	 */
	public function get_body() {
		return $this->body;
	}

	/**
	 * Runs a XPath query. Returns the results in an iterable DOMNodeList.
	 *
	 * @link  https://www.php.net/manual/en/domxpath.query.php
	 *
	 * @since 3.6.2
	 *
	 * @param string  $query            The query to run.
	 * @param DOMNode $node             (Optional) Make query relative to this node (context node).
	 * @param bool    $register_node_ns (Optional). When false, disables registering the context node.
	 *
	 * @return DOMNodeList|bool On success returns list; else false.
	 */
	public function query( $query, $node = null, $register_node_ns = true ) {
		if ( ! $this->xpath instanceof DOMXpath ) {
			return false;
		}

		return $this->xpath->query( $query, $node, $register_node_ns );
	}

	/**
	 * Loads HTML from a string.
	 *
	 * Note: Suppresses internal errors in the case of malformed HTML.
	 *
	 * @link  https://php.net/manual/domdocument.loadhtml.php
	 *
	 * @since 3.6.2
	 *
	 * @param string     $html    The given HTML string.
	 * @param string|int $options (Optional) Since PHP 5.4.0 and Libxml 2.6.0, you may also use the options parameter to
	 *                            specify additional Libxml parameters.
	 *
	 * @return bool true on success; else false.
	 */
	public function loadHTML( $html, $options = 0 ) {
		if ( empty( $html ) ) {
			return false;
		}

		$internal_errors = libxml_use_internal_errors( true );

		$success = parent::loadHTML( $html, $options );

		libxml_clear_errors();
		libxml_use_internal_errors( $internal_errors );

		return $success;
	}

	/**
	 * Prepares the given HTML string with the defined encoding.
	 *
	 * @since 3.6.2.1
	 *
	 * @param string $html HTML to prepare.
	 *
	 * @return string
	 */
	protected function prepare_html( $html ) {
		return mb_convert_encoding( $html, 'HTML-ENTITIES', $this->current_encoding );
	}

	/**
	 * Initializes the DOMXpath instance, which is used for query.
	 *
	 * @link  https://www.php.net/manual/en/domxpath.construct.php
	 *
	 * @since 3.6
	 */
	protected function init_xpath() {
		$this->xpath = new DOMXpath( $this );
	}

	/**
	 * Reset the internal optimizations of the HTMLDocument object.
	 *
	 * Why? Needed when doing an operation that causes the cached nodes and XPath objects to point to the wrong
	 * document.
	 *
	 * @since 3.6
	 *
	 * @return self Reset version of the Document object.
	 */
	private function reset() {
		// Drop references to old DOM document.
		unset( $this->xpath, $this->head, $this->body );

		return $this;
	}

	/**
	 * Initializes the encoding.
	 *
	 * @since 3.6.2.1
	 *
	 * @param string $encoding The encoding of the document as part of the XML declaration.
	 */
	private function init_encoding( $encoding ) {
		if ( ! empty( $encoding ) ) {
			$this->current_encoding = (string) $encoding;

			return;
		}

		if ( function_exists( 'get_option' ) ) {
			$this->current_encoding = get_option( 'blog_charset', self::DEFAULT_ENCODING );
		}

		if ( empty( $this->current_encoding ) ) {
			$this->current_encoding = self::DEFAULT_ENCODING;
		}
	}

	/**
	 * Make sure we properly reinitialize on clone.
	 *
	 * @since 3.6
	 */
	public function __clone() {
		$this->reset();
	}
}
