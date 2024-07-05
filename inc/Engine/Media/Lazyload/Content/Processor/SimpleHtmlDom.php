<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Lazyload\Content\Processor;

use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDomBlank;

class SimpleHtmlDom {
	public function add_locations_hash_to_html( $html ) {
		$dom = HtmlDomParser::str_get_html( $html );

		$body = $dom->getElementByTagName( 'body' );

		if ( $body instanceof SimpleHtmlDomBlank ) {
			return $html;
		}

		$this->add_hash_to_element( $body, 2 );

		return $dom->save();
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
