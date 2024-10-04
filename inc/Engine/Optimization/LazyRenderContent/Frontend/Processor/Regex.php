<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor;

use WP_Rocket\Logger\Logger;

class Regex implements ProcessorInterface {

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
	private $exclusions; // @phpstan-ignore-line

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

		$this->max_hashes = $this->get_max_tags();
		$this->count      = 0;

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
		$processed_tags = $this->get_processed_tags();

		$result = preg_match_all( '/(?><(' . implode( '|', $processed_tags ) . ')[^>]*>)/is', $element, $matches, PREG_SET_ORDER );

		if ( ! $result ) {
			Logger::error( 'No elements found in the HTML content.', [ 'LazyRenderContent' ] );

			return $html;
		}

		foreach ( $matches as $child ) {

			if ( $this->count >= $this->max_hashes ) {
				Logger::warning( 'Stopping LRC hash injection as max_hashes is reached.', [ 'LazyRenderContent' ] );
				return $html;
			}

			// Calculate the hash of the opening tag.
			$opening_tag_html = strstr( $child[0], '>', true ) . '>';

			$hash = md5( $opening_tag_html . $this->count );

			++$this->count;

			// Add the data-rocket-location-hash attribute.
			$replace = preg_replace( '/' . $child[1] . '/is', '$0 data-rocket-location-hash="' . $hash . '"', $child[0], 1 );

			$html = preg_replace( '/' . preg_quote( $child[0], '/' ) . '/', $replace, $html, 1 );
		}

		return $html;
	}
}
