<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor;

use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDomBlank;
use voku\helper\SimpleHtmlDomInterface;
use WP_Rocket\Logger\Logger;

class SimpleHtmlDom implements ProcessorInterface {

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
		$dom = HtmlDomParser::str_get_html( $html );

		$body = $dom->getElementByTagName( 'body' );

		if ( $body instanceof SimpleHtmlDomBlank ) {
			Logger::error( 'Body element not found in the HTML content.', [ 'LazyRenderContent' ] );

			return $html;
		}

		$this->max_hashes = $this->get_max_tags();
		$this->count      = 0;

		$this->add_hash_to_element( $body, $this->get_depth() );

		return $dom->save();
	}

	/**
	 * Add a hash to the element and its children.
	 *
	 * @param SimpleHtmlDomInterface $element The element to add the hash to.
	 * @param int                    $depth   The depth of the recursion.
	 */
	private function add_hash_to_element( $element, $depth ) {
		if ( $depth < 0 ) {
			return;
		}

		$processed_tags = $this->get_processed_tags();

		foreach ( $element->childNodes() as $child ) {

			if ( $this->count >= $this->max_hashes ) {
				Logger::warning( 'Stopping LRC hash injection as max_hashes is reached.', [ 'LazyRenderContent' ] );
				return;
			}

			if ( ! in_array( strtoupper( $child->getTag() ), $processed_tags, true ) ) {
				continue;
			}

			// Calculate the hash of the opening tag.
			$child_html       = $child->html();
			$opening_tag_html = strstr( $child_html, '>', true ) . '>';

			$hash = md5( $opening_tag_html . $this->count );

			++$this->count;

			// Add the data-rocket-location-hash attribute.
			$child->setAttribute( 'data-rocket-location-hash', $hash );

			// Recursively process child elements.
			$this->add_hash_to_element( $child, $depth - 1 );
		}
	}
}
