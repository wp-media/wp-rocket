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
	const ENCODING = 'utf-8';

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
	 * @param string $version  Optional. The version number of the document as part of the XML declaration.
	 * @param string $encoding Optional. The encoding of the document as part of the XML declaration.
	 */
	public function __construct( $version = '1.0', $encoding = null ) {
		if ( ! empty( $encoding ) ) {
			$encoding = (string) $encoding;
		} elseif ( function_exists( 'bloginfo' ) ) {
			$encoding = get_bloginfo( 'charset' );
		} else {
			$encoding = self::ENCODING;
		}

		$version = (string) $version;
		if ( empty( $version ) ) {
			$version = self::VERSION;
		}

		parent::__construct( $version, $encoding );
	}

	/**
	 * Named constructor for transforming HTML into DOM.
	 *
	 * @param string $html     HTML to transform into HTML DOMDocument object.
	 * @param string $version  Optional. The version number of the document as part of the XML declaration.
	 * @param string $encoding Optional. The encoding of the document as part of the XML declaration.
	 *
	 * @return HTMLDocument|false DOM generated from provided HTML, or false if the transformation failed.
	 */
	public static function from_html( $html, $version = '', $encoding = null ) {
		$dom = new self( $version, $encoding );

		if ( ! $dom->loadHTML( $html ) ) {
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
	 * @param string $fragment The HTML fragment to transform into HTML DOMDocument object.
	 * @param string $version  Optional. The version number of the document as part of the XML declaration.
	 * @param string $encoding Optional. The encoding of the document as part of the XML declaration.
	 *
	 * @return HTMLDocument|false DOM generated from provided HTML, or false if the transformation failed.
	 */
	public static function from_fragment( $fragment, $version = '', $encoding = null ) {
		$dom = new self( $version, $encoding );

		// @TODO Need to check versions.
		$options = LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD;

		if ( ! $dom->loadHTML( $fragment, $options ) ) {
			return false;
		}

		$dom->init_xpath();

		return $dom;
	}

	/**
	 * Initializes the DOMXpath instance, which is used for query.
	 */
	protected function init_xpath() {
		$this->xpath = new DOMXpath( $this );
	}

	/**
	 * Gets the HTML in string format.
	 *
	 * @return string
	 */
	public function get_html() {
		return $this->saveHTML();
	}

	/**
	 * Returns the <head> DOMElement.
	 *
	 * @return DOMElement
	 */
	public function get_head() {
		return $this->head;
	}

	/**
	 * Returns the <body> DOMElement.
	 *
	 * @return DOMElement
	 */
	public function get_body() {
		return $this->body;
	}

	/**
	 * Runs a XPath query. Returns the results in an iterable DOMNodeList.
	 *
	 * @link https://www.php.net/manual/en/domxpath.query.php
	 *
	 * @param string  $query            The query to run.
	 * @param DOMNode $node             (Optional) Make query relative to this node (context node).
	 * @param bool    $register_node_ns (Optional). When false, disables registering the context node.
	 *
	 * @return DOMNodeList|bool On success returns list; else false.
	 */
	public function query( $query, $node = null, $register_node_ns = true ) {
		return $this->xpath->query( $query, $node, $register_node_ns );
	}

	/**
	 * Loads HTML from a string.
	 *
	 * Note: Suppresses internal errors in the case of malformed HTML.
	 *
	 * @link https://php.net/manual/domdocument.loadhtml.php
	 *
	 * @param string     $html    The given HTML string.
	 * @param string|int $options (Optional) Since PHP 5.4.0 and Libxml 2.6.0, you may also use the options parameter to
	 *                            specify additional Libxml parameters.
	 *
	 * @return bool true on success; else false.
	 */
	public function loadHTML( $html, $options = 0 ) {
		$internal_errors = libxml_use_internal_errors( true );

		$success = parent::loadHTML( $html, $options );

		libxml_clear_errors();
		libxml_use_internal_errors( $internal_errors );

		return $success;
	}

	/**
	 * Reset the internal optimizations of the HTMLDocument object.
	 *
	 * Why? Needed when doing an operation that causes the cached nodes and XPath objects to point to the wrong
	 * document.
	 *
	 * @return self Reset version of the Document object.
	 */
	private function reset() {
		// Drop references to old DOM document.
		unset( $this->xpath, $this->head, $this->body );

		return $this;
	}

	/**
	 * Make sure we properly reinitialize on clone.
	 *
	 * @return void
	 */
	public function __clone() {
		$this->reset();
	}
}
