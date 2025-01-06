<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts;

trait FontsTrait {

	/**
	 * Get the list of patterns to exclude from media fonts rewrite.
	 *
	 * @return string[]
	 */
	protected function get_exclusions(): array {
		/**
		 * Filters the list of patterns to exclude from media font rewrite.
		 *
		 * @since 3.18
		 *
		 * @param string[] $exclusions The list of patterns to exclude from media fonts.
		 */
		return wpm_apply_filters_typed( 'string[]', 'rocket_exclude_locally_host_fonts', [] );
	}

	/**
	 * Checks if a font is excluded based on the provided exclusions.
	 *
	 * @param string   $subject    The string to check.
	 * @param string[] $exclusions The list of exclusions.
	 *
	 * @return bool True if the URL is excluded, false otherwise.
	 */
	protected function is_excluded( string $subject, array $exclusions ): bool {
		// Bail out early if there are no exclusions.
		if ( empty( $exclusions ) ) {
			return false;
		}

		// Escape each exclusion pattern to prevent regex issues.
		$escaped_exclusions = array_map(
				function ( $exclusion ) {
					$query_string = preg_replace( '@(https?:)?(//)?fonts\.googleapis\.com/css2?\?@i', '', $exclusion );

					return str_replace(
						[ '#', '+', '=' ],
						[ '\#', '\+', '\=' ],
						$query_string
					);
				},
			$exclusions
			);

		// Combine all patterns into a single regex string.
		$exclusions_str = implode( '|', $escaped_exclusions );

		// Check the URL against the combined regex pattern.
		return (bool) preg_match( '#(' . $exclusions_str . ')#i', $subject );
	}
}
