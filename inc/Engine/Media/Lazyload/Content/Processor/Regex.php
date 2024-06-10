<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Lazyload\Content\Processor;

class Regex {
	public function add_locations_hash_to_html( $html ) {
		$result = preg_match( '/(?><body[^>]*>)(?>.*?<\/body>)/is', $html, $matches );

		if ( ! $result ) {
			return $html;
		}

		return $this->add_hash_to_element( $html, $matches[0], 2 );
	}

	private function add_hash_to_element( $html, $element, $depth ) {
		if ( $depth < 0 ) {
			return $html;
		}

		$skip_tags = [
			'div',
			'main',
			'footer',
			'section',
			'article',
			'header',
		];

		$result = preg_match_all( '/(?><(' . implode( '|', $skip_tags ) . ')[^>]*>)/is', $element, $matches, PREG_SET_ORDER );

		if ( ! $result ) {
			return $html;
		}

		foreach ( $matches as $child ) {
			// Calculate the hash of the opening tag.
			$opening_tag_html = strstr( $child[0], '>', true ) . '>';

			$hash = md5( $opening_tag_html );

			// Add the data-rocket-location-hash attribute.
			$replace = preg_replace( '/' . $child[1] . '/is', '$0 data-rocket-location-hash="' . $hash . '"', $child[0], 1 );

			$html = preg_replace( '/' . preg_quote( $child[0], '/' ) . '/', $replace, $html, 1 );
		}

		return $html;
	}
}
