<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor;

use WP_Rocket\Logger\Logger;

class Regex implements ProcessorInterface {
	/**
	 * Add hashes to the HTML elements
	 *
	 * @param string $html The HTML content.
	 *
	 * @return string
	 */
	public function add_hashes( $html ) {
		$result = preg_match( '/(?><body[^>]*>)(?>.*?<\/body>)/is', $html, $matches );

		if ( ! $result ) {
			Logger::error( 'Body element not found in the HTML content.', [ 'LazyRenderContent' ] );

			return $html;
		}

		return $this->add_hash_to_element( $html, $matches[0] );
	}

	/**
	 * Add a hash to the element and its children.
	 *
	 * @param string $html   The HTML content.
	 * @param string $element The element to add the hash to.
	 *
	 * @return string
	 */
	private function add_hash_to_element( $html, $element ) {
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
			Logger::error( 'No elements found in the HTML content.', [ 'LazyRenderContent' ] );

			return $html;
		}

		$count = 0;

		foreach ( $matches as $child ) {
			// Calculate the hash of the opening tag.
			$opening_tag_html = strstr( $child[0], '>', true ) . '>';

			$hash = md5( $opening_tag_html . $count );

			++$count;

			// Add the data-rocket-location-hash attribute.
			$replace = preg_replace( '/' . $child[1] . '/is', '$0 data-rocket-location-hash="' . $hash . '"', $child[0], 1 );

			$html = preg_replace( '/' . preg_quote( $child[0], '/' ) . '/', $replace, $html, 1 );
		}

		return $html;
	}
}
