<?php

namespace WP_Rocket\Engine\Preload\Controller;

trait CheckExcludedTrait {

	/**
	 * Add new pattern of excluded uri.
	 *
	 * @param array $regexes regexes used to exclude urls.
	 * @return array
	 */
	public function add_cache_reject_uri_to_excluded( array $regexes ): array {
		$regexes[] = get_rocket_cache_reject_uri();

		return $regexes;
	}

	/**
	 * Check if the url is excluded by using a filter.
	 *
	 * @param string $url url to check.
	 * @return bool
	 */
	protected function is_excluded_by_filter( string $url ): bool {
		/**
		 * Regex to exclude URI from preload.
		 *
		 * @param string[] regexes to check
		 */
		$regexes = (array) apply_filters( 'rocket_preload_exclude_urls', [] );

		if ( empty( $regexes ) ) {
			return false;
		}

		$regexes = array_unique( $regexes );
		$url     = user_trailingslashit( $url );

		foreach ( $regexes as $regex ) {
			if ( ! is_string( $regex ) ) {
				continue;
			}

			$regex = user_trailingslashit( $regex );

			if ( preg_match( "@$regex@", $url ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Indicate if we need to exclude the url.
	 *
	 * @param string $url url to check.
	 * @return bool
	 */
	protected function is_url_excluded( string $url ): bool {
		$queries          = wp_parse_url( $url, PHP_URL_QUERY );
		$queries          = explode( '&', $queries );
		$queries          = array_map(
			function ( $query ) {
				$query = explode( '=', $query );
				return array_shift( $query );
			},
			$queries
		);
		$excluded_queries = rocket_get_ignored_parameters();

		return count( array_intersect( $queries, array_keys( $excluded_queries ) ) ) > 0 || $this->is_excluded_by_filter( $url );
	}
}
