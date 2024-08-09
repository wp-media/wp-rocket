<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor;

use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDomBlank;
use WP_Rocket\Logger\Logger;

class SimpleHtmlDom implements ProcessorInterface {
	/**
	 * Add hashes to the HTML elements
	 *
	 * @param string $html The HTML content.
	 *
	 * @return string
	 */
	public function add_hashes( $html ) {
		$dom = HtmlDomParser::str_get_html( $html );

		$body = $dom->getElementByTagName( 'body' );

		if ( $body instanceof SimpleHtmlDomBlank ) {
			Logger::error( 'Body element not found in the HTML content.', [ 'LazyRenderContent' ] );

			return $html;
		}

		$this->add_hash_to_element( $body, 2 );

		return $dom->save();
	}

	/**
	 * Add a hash to the element and its children.
	 *
	 * @param SimpleHtmlDomBlank $element The element to add the hash to.
	 * @param int                $depth   The depth of the recursion.
	 */
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

		foreach ( $element->childNodes() as $child ) {
			if ( ! in_array( strtoupper( $child->getTag() ), $skip_tags, true ) ) {
				continue;
			}

			// Calculate the hash of the opening tag.
			$child_html       = $child->html();
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
