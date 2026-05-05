<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Drivers;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\RegexTrait;

class RocketCDNPaid implements DriverInterface {
	use RegexTrait;

	/**
	 * Options data for accessing excluded pages
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor.
	 *
	 * @param Options_Data $options Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Should rewrite url or not.
	 *
	 * @param string $url Page Url to check.
	 * @return bool
	 */
	public function should_rewrite_url( string $url ): bool {
		// Get excluded pages from options.
		$excluded_pages = $this->get_excluded_pages();

		// Normalize URL for comparison.
		$normalized_url = untrailingslashit( $url );

		// Check if URL is in excluded list.
		foreach ( $excluded_pages as $excluded_pattern ) {
			if ( $this->matches_pattern( $normalized_url, $excluded_pattern ) ) {
				return false;  // This page is excluded, don't rewrite.
			}
		}

		return true;
	}

	/**
	 * Get excluded pages from options
	 *
	 * @return array
	 */
	private function get_excluded_pages(): array {
		$excluded = $this->options->get( 'cdn_reject_pages', [] );
		return is_array( $excluded ) ? $excluded : [];
	}

	/**
	 * Check if URL matches exclusion pattern
	 *
	 * @param string $url URL to check.
	 * @param string $pattern Pattern to match against.
	 * @return bool
	 */
	private function matches_pattern( string $url, string $pattern ): bool {
		return ! empty(
			$this->find(
				preg_quote( $pattern, '/' ),
				$url
			)
		);
	}
}
