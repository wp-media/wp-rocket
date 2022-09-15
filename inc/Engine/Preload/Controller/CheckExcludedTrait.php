<?php

namespace WP_Rocket\Engine\Preload\Controller;

trait CheckExcludedTrait {

	/**
	 * Check if the url is excluded.
	 *
	 * @param string $url url to check.
	 * @return bool
	 */
	protected function is_excluded( string $url ) {
		$excluded = str_replace( '/', '\/', get_rocket_cache_reject_uri() );
		return (bool) preg_match( "/$excluded/", $url );
	}

	/**
	 * Check if the url is excluded by using a filter.
	 *
	 * @param string $url url to check.
	 * @return bool
	 */
	protected function is_excluded_by_filter( string $url ): bool {
		$regexes = apply_filters( 'rocket_preload_exclude_urls', [] );

		if ( ! $regexes ) {
			return false;
		}

		foreach ( $regexes as $regex ) {
			if ( preg_match( '/' . $regex . '/', $url ) ) {
				return true;
			}
		}
		return false;
	}
}
