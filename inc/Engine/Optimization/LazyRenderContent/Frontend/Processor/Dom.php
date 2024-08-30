<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor;

use DOMDocument;
use WP_Rocket\Logger\Logger;

class Dom implements ProcessorInterface {

	use HelperTrait;

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

		$this->add_hash_to_element( $body, $this->get_depth() );

		return $dom->saveHTML();
	}

	/**
	 * Add a hash to the element and its children.
	 *
	 * @param \DOMElement $element The element to add the hash to.
	 * @param int         $depth   The depth of the recursion.
	 */
	private function add_hash_to_element( $element, $depth ) {
		if ( $depth < 0 ) {
			return;
		}

		$processed_tags = $this->get_processed_tags();

		static $count = 0;

		foreach ( $element->childNodes as $child ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
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

			$hash = md5( $opening_tag_html . $count );

			++$count;

			// Add the data-rocket-location-hash attribute.
			$child->setAttribute( 'data-rocket-location-hash', $hash );

			// Recursively process child elements.
			$this->add_hash_to_element( $child, $depth - 1 );
		}
	}
}
