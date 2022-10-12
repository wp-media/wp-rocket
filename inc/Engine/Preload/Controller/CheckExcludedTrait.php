<?php

namespace WP_Rocket\Engine\Preload\Controller;

trait CheckExcludedTrait {

	/**
	 * Add new pattern of excluded uri.
	 *
	 * @param array $regexes regexes used to exclude urls.
	 * @return array
	 */
	public function add_cache_reject_uri_to_excluded( array $regexes ) {
		$regexes[] = str_replace( '/', '\/', get_rocket_cache_reject_uri() );
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

		$regexes = array_flip( array_flip( $regexes ) );

		foreach ( $regexes as $regex ) {
			if ( ! is_string( $regex ) ) {
				continue;
			}

			if ( preg_match( "@$regex@", $url ) ) {
				return true;
			}
		}
		return false;
	}
}
