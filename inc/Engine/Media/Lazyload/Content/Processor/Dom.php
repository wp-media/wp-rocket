<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Lazyload\Content\Processor;

use DOMDocument;

class Dom {
	public function add_locations_hash_to_html( $html ) {
		// Load HTML into DOMDocument.
		$dom = new DOMDocument();

		@$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

		// Get the body element.
		$body = $dom->getElementsByTagName( 'body' )->item( 0 );

		if ( ! $body ) {
			return $html;
		}

		$this->add_hash_to_element( $body, 2 );

		// Output the modified HTML.
		return $dom->saveHTML();
	}

	private function add_hash_to_element( $element, $depth ) {
		if ( $depth < 0 ) {
			return;
		}

		$skip_tags = [
			'DIV',
			'MAIN',
			'FOOTER',
			'SECTION',
			'ARTICLE',
			'HEADER',
		];

		static $count = 0;

		foreach ( $element->childNodes as $child ) {
			if (
				XML_ELEMENT_NODE !== $child->nodeType
				||
				! in_array( strtoupper( $child->tagName ), $skip_tags, true )
			) {
				continue;
			}

			// Calculate the hash of the opening tag.
			$child_html       = $child->ownerDocument->saveHTML( $child );
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
