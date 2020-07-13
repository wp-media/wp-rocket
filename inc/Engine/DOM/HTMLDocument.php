<?php

namespace WP_Rocket\Engine\DOM;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXpath;
use WP_Rocket\Engine\DOM\Transformer\Transformer;
use WP_Rocket\Engine\DOM\Transformer\TransformerInterface;

/**
 * Props to AMP Project Contributors https://github.com/ampproject/amp-wp/graphs/contributors as parts of this class are
 * borrowed and/or adapted from the plugin.
 */
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
	 * Dotted version for the Libxml library.
	 *
	 * @var string
	 */
	private static $libxml_version;

	/**
	 * Instance of the HTML Transformer.
	 *
	 * @var TransformerInterface
	 */
	private $transformer;

	/**
	 * Flag to indicate if the DOM structure should be normalized.
	 *
	 * @var bool
	 */
	private $normalize = true;

	/**
	 * Creates a new HTML DOMDocument object.
	 *
	 * @link  https://php.net/manual/domdocument.construct.php
	 *
	 * @since 3.6.2
	 *
	 * @param string $version            Optional. The version number of the document as part of the XML declaration.
	 * @param string $encoding           Optional. The encoding of the document as part of the XML declaration.
	 * @param bool   $enable_transformer Optional. When false, no transformations. Default: true.
	 */
	public function __construct( $version = '1.0', $encoding = null, $enable_transformer = true ) {
		$this->init_encoding( $encoding );

		$version = (string) $version;
		if ( empty( $version ) ) {
			$version = self::VERSION;
		}

		self::$libxml_version = self::get_libxml_version();
		if ( $enable_transformer ) {
			$this->setTransformer();
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

		$dom            = new self( $version, $encoding );
		$dom->normalize = true;

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
	 * @since 3.6.2
	 *
	 * @param string $fragment           The HTML fragment to transform into HTML DOMDocument object.
	 * @param string $version            Optional. The version number of the document as part of the XML declaration.
	 * @param string $encoding           Optional. The encoding of the document as part of the XML declaration.
	 * @param bool   $enable_transformer Optional. When false, no transformations. Default: true.
	 *
	 * @return HTMLDocument|false DOM generated from provided HTML, or false if the transformation failed.
	 */
	public static function from_fragment( $fragment, $version = '', $encoding = null, $enable_transformer = true ) {
		if ( empty( $fragment ) ) {
			return false;
		}

		$dom            = new self( $version, $encoding, $enable_transformer );
		$dom->normalize = false;

		$options = 0;
		// LIBXML_HTML_NOIMPLIED is only available for libxml >= 2.7.7.
		// Turns off the automatic adding of implied html/body... elements.
		if ( defined( 'LIBXML_HTML_NOIMPLIED' ) ) {
			$options |= constant( 'LIBXML_HTML_NOIMPLIED' );
		}

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
		return $this->saveHTML();
	}

	/**
	 * Dumps the internal document into a string using HTML formatting.
	 *
	 * @since 3.6.2.1
	 *
	 * @link  https://php.net/manual/domdocument.savehtml.php
	 *
	 * @param DOMNode|null $node Optional. When provided, dumps as a string.
	 *
	 * @return bool|string HTML string on success; else false.
	 */
	public function saveHTML( DOMNode $node = null ) {
		$html = parent::saveHTML( $node );
		if ( empty( $html ) ) {
			return false;
		}

		if ( empty( $this->transformer ) ) {
			return $html;
		}

		return $this->transformer->restore( $html );
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
	 * @param bool    $return_as_array  (Optional). When false, returns as DOMNodeList; else, array.
	 *
	 * @return DOMNodeList|array|bool On success returns list; else false.
	 */
	public function query( $query, $node = null, $register_node_ns = true, $return_as_array = true ) {
		if ( ! $this->xpath instanceof DOMXpath ) {
			return false;
		}

		$results = $this->xpath->query( $query, $node, $register_node_ns );
		if ( ! $return_as_array ) {
			return $results;
		}

		return $results instanceof DOMNodeList ? iterator_to_array( $results ) : false;
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
		if ( ! is_string( $html ) || '' === $html ) {
			return false;
		}

		$html = trim( $html );
		if ( empty( $html ) ) {
			return false;
		}

		if ( ! empty( $this->transformer ) ) {
			$html = $this->transformer->replace( $html, $this->normalize );
			if ( empty( $html ) ) {
				return false;
			}
		}

		$internal_errors = libxml_use_internal_errors( true );

		$success = parent::loadHTML( $html, $this->get_libxml_options( $options ) );

		libxml_clear_errors();
		libxml_use_internal_errors( $internal_errors );

		return $success;
	}

	/**
	 * Transformer setter injection.
	 *
	 * Why is the transformer not injected into the constructor?
	 * The transformer requires this class to set up and initialize before creating the transformer.
	 *
	 * Why a separate method?
	 *      1. Encapsulates the knowledge of how to create a transformer.
	 *      2. For testing.
	 *      3. Extensibility.
	 *
	 * @since 3.6.2.1
	 *
	 * @param TransformerInterface|null $transformer Instance of the HTML Transformer.
	 */
	protected function setTransformer( TransformerInterface $transformer = null ) {
		$this->transformer = null === $transformer
			? new Transformer( $this->current_encoding )
			: $transformer;
	}

	/**
	 * Gets the Libxml options for DOMDocument::loadHTML().
	 *
	 * @link  https://www.php.net/manual/en/libxml.constants.php
	 *
	 * @since 3.6.2.1
	 *
	 * @param int $options Libxml options.
	 *
	 * @return int Returns libxml options.
	 */
	private function get_libxml_options( $options = 0 ) {
		// LIBXML_COMPACT is only available for libxml >= 2.6.21.
		// Activates small nodes allocation optimization.
		if ( defined( 'LIBXML_COMPACT' ) ) {
			$options |= constant( 'LIBXML_COMPACT' );
		}

		// LIBXML_HTML_NODEFDTD is only available for libxml >= 2.7.8.
		// Prevents a default doctype being added when one is not found.
		if ( defined( 'LIBXML_HTML_NODEFDTD' ) ) {
			$options |= constant( 'LIBXML_HTML_NODEFDTD' );
		}

		// LIBXML_SCHEMA_CREATE is only available for libxml >= 2.6.14.
		// Ensures DOMDocument does not strip closing HTML tags within a <script> element.
		if ( defined( 'LIBXML_SCHEMA_CREATE' ) ) {
			$options |= constant( 'LIBXML_SCHEMA_CREATE' );
		}

		return $options;
	}

	/**
	 * Initializes the DOMXpath instance, which is used for query.
	 *
	 * @link  https://www.php.net/manual/en/domxpath.construct.php
	 *
	 * @since 3.6.2
	 */
	private function init_xpath() {
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

		$this->transformer->reset();
		$this->normalize = true;

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
	 * Gets the dotted version of the server's Libxml library, if exists.
	 *
	 * @since 3.6.2.1
	 *
	 * @return string
	 */
	public static function get_libxml_version() {
		if ( static::$libxml_version ) {
			return static::$libxml_version;
		}

		if ( defined( 'LIBXML_DOTTED_VERSION' ) ) {
			return constant( 'LIBXML_DOTTED_VERSION' );
		}

		return '1.0';
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
