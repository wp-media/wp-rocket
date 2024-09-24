<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor;

use DOMDocument;
use WP_Rocket\Logger\Logger;

class Dom implements ProcessorInterface {
	use HelperTrait;

	/**
	 * Number of injects hashes.
	 *
	 * @since 3.17
	 *
	 * @var int
	 */
	private $count;

	/**
	 * Maximum number of hashes to inject.
	 *
	 * @since 3.17
	 *
	 * @var int
	 */
	private $max_hashes;

	/**
	 * Array of patterns to exclude from hash injection.
	 *
	 * @since 3.17.0.2
	 *
	 * @var array
	 */
	private $exclusions = [];

	/**
	 * Sets the exclusions list
	 *
	 * @param string[] $exclusions The list of patterns to exclude from hash injection.
	 *
	 * @return void
	 */
	public function set_exclusions( $exclusions ): void {
		$this->exclusions = $exclusions;
	}

	/**
	 * Gets the exclusions pattern
	 *
	 * @return string
	 */
	private function get_exclusions_pattern(): string {
		if ( empty( $this->exclusions ) ) {
			return '';
		}

		$exclusions = array_map(
			function ( $exclusion ) {
				return preg_quote( $exclusion, '/' );
			},
			$this->exclusions
		);

		return implode( '|', $exclusions );
	}

	/**
	 * Add hashes to the HTML elements
	 *
	 * @param string $html The HTML content.
	 *
	 * @return string
	 */
	public function add_hashes( $html ) {
		$internal_errors = libxml_use_internal_errors( true );

		// Load HTML into DOMDocument.
		$dom = new DOMDocument();

		if ( ! $dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD ) ) {
			foreach ( libxml_get_errors() as $error ) {
				Logger::error( $error->message, [ 'LazyRenderContent' ] );
			}

			libxml_clear_errors();

			return $html;
		}

		libxml_use_internal_errors( $internal_errors );

		// Get the body element.
		$body = $dom->getElementsByTagName( 'body' )->item( 0 );

		if ( ! $body ) {
			Logger::error( 'Body element not found in the HTML content.', [ 'LazyRenderContent' ] );

			return $html;
		}

		$this->max_hashes = $this->get_max_tags();
		$this->count      = 0;

		return $this->add_hash_to_element( $body, $this->get_depth(), $html );
	}

	/**
	 * Add a hash to the element and its children.
	 *
	 * @param \DOMElement $element The element to add the hash to.
	 * @param int         $depth   The depth of the recursion.
	 * @param string      $html    The HTML content.
	 *
	 * @return string
	 */
	private function add_hash_to_element( $element, $depth, $html ) {
		if ( $depth < 0 ) {
			return $html;
		}

		$processed_tags = $this->get_processed_tags();

		foreach ( $element->childNodes as $child ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

			if ( $this->count >= $this->max_hashes ) {
				Logger::warning( 'Stopping LRC hash injection as max_hashes is reached.', [ 'LazyRenderContent' ] );
				return $html;
			}

			if ( ! $child instanceof \DOMElement ) {
				continue;
			}

			if (
				XML_ELEMENT_NODE !== $child->nodeType // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				||
				! in_array( strtoupper( $child->tagName ), $processed_tags, true ) // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			) {
				continue;
			}

			// Calculate the hash of the opening tag.
			$child_html       = $child->ownerDocument->saveHTML( $child ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$opening_tag_html = strstr( $child_html, '>', true ) . '>';

			$exclusions_pattern = $this->get_exclusions_pattern();

			if (
				! empty( $exclusions_pattern )
				&&
				preg_match( '/(' . $exclusions_pattern . ')/i', $opening_tag_html )
			) {
				continue;
			}

			$hash = md5( $opening_tag_html . $this->count );

			++$this->count;

			// Inject the hash as an attribute in the opening tag.
			$replace = preg_replace( '/' . $child->tagName . '/is', '$0 data-rocket-location-hash="' . $hash . '"', $opening_tag_html, 1 ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( is_null( $replace ) ) {
				continue;
			}
			// Replace the opening tag in the HTML by the manipulated one
			// If DOMDocument automatically modified the original element, we might not find it in the HTML.
			// Known issue: if there is an element with the exact same opening tag before in the HTML that did not receive a hash, it will replaced instead of the correct element in the HTML.
			$element_replacements = 0;
			$modified_html        = preg_replace( '/' . preg_quote( $opening_tag_html, '/' ) . '/', $replace, $html, 1, $element_replacements );
			if ( $element_replacements < 1 ) {
				Logger::warning( 'Opening tag from DOMDocument not found in original HTML.', [ 'LazyRenderContent' ] );
			}
			if ( is_null( $modified_html ) ) {
				continue;
			}
			$html = $modified_html;

			// Recursively process child elements.
			$html = $this->add_hash_to_element( $child, $depth - 1, $html );
		}

		return $html;
	}
}
