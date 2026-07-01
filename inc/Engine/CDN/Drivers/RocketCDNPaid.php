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

		// Check if URL is in excluded list.
		foreach ( $excluded_pages as $excluded_pattern ) {
			$normalized_pattern = $this->normalize_pattern( $excluded_pattern );
			if ( $this->matches_pattern( $url, $normalized_pattern ) ) {
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
		if ( empty( $pattern ) ) {
			return false; // Empty pattern should not match anything.
		}

		return ! empty(
			$this->find(
				preg_quote( $pattern, '/' ),
				$url
			)
		);
	}

	/**
	 * Normalize pattern for matching
	 *
	 * Handles:
	 * 1. Trailing slashes (e.g., /category/ => /category)
	 * 2. Non-Latin characters (e.g., категории => URL-encoded)
	 * 3. Inconsistent URL encoding
	 *
	 * @param string $pattern Pattern to normalize.
	 * @return string Normalized pattern.
	 */
	private function normalize_pattern( string $pattern ): string {
		$pattern = untrailingslashit( $pattern );

		// This ensures "категории" becomes "%d0%ba%d0%b0%d1%82%d0%b5%d0%b3%d0%be%d1%80%d0%b8%d0%b8".
		$pattern = $this->encode_non_latin_chars( $pattern );

		return $pattern;
	}

	/**
	 * URL-encode non-Latin characters in pattern
	 *
	 * Converts UTF-8 characters (like Cyrillic, Arabic, CJK) to URL-encoded form.
	 * This matches how WordPress and browsers encode URLs.
	 *
	 * @param string $pattern Pattern with possible non-Latin chars.
	 * @return string Pattern with non-Latin chars URL-encoded.
	 */
	private function encode_non_latin_chars( string $pattern ): string {
		if ( empty( $pattern ) ) {
			return '';
		}
		// Use rawurlencode to match browser URL encoding.
		// But preserve the path structure (don't encode /).
		$parts         = explode( '/', $pattern );
		$encoded_parts = array_map( 'rawurlencode', $parts );

		return implode( '/', $encoded_parts );
	}
}
