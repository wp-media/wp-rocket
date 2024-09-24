<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor;

interface ProcessorInterface {
	/**
	 * Add hashes to the HTML elements
	 *
	 * @param string $html The HTML content.
	 *
	 * @return string
	 */
	public function add_hashes( $html );

	/**
	 * Sets the exclusions list
	 *
	 * @param string[] $exclusions The list of patterns to exclude from hash injection.
	 *
	 * @return void
	 */
	public function set_exclusions( array $exclusions ): void;
}
