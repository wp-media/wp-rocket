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
		$user_added_cache_reject_uri = (array) get_rocket_option( 'cache_reject_uri', [] );

		if ( count( $user_added_cache_reject_uri ) === 0 ) {
			return $regexes;
		}

		$altered_user_added_cache_reject_uri = implode( '$|', $user_added_cache_reject_uri );

		$user_added_cache_reject_uri = implode( '|', $user_added_cache_reject_uri );
		$cache_reject_uri            = get_rocket_cache_reject_uri();

		$regexes[] = str_replace( $user_added_cache_reject_uri . '|', $altered_user_added_cache_reject_uri . '$|', $cache_reject_uri );

		return $regexes;
	}

	/**
	 * Check if the url is excluded by using a filter.
	 *
	 * @param string $url url to check.
	 * @return bool
	 */
	protected function is_excluded_by_filter( string $url ): bool {
		global $wp_rewrite;
		$pagination_regex = "/$wp_rewrite->pagination_base/\d+";
		/**
		 * Regex to exclude URI from preload.
		 *
		 * @param string[] regexes to check
		 */
		$regexes = (array) apply_filters( 'rocket_preload_exclude_urls', [ $pagination_regex ] );

		if ( empty( $regexes ) ) {
			return false;
		}

		$regexes = array_unique( $regexes );
		$url     = strtok( $url, '?' );
		$url     = user_trailingslashit( $url );

		foreach ( $regexes as $regex ) {
			if ( ! is_string( $regex ) ) {
				continue;
			}

			$regex = user_trailingslashit( $regex );

			if ( preg_match( "@$regex$@m", $url ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Checks that a page is private.
	 *
	 * @param   string $url  Page url.
	 *
	 * @return  bool         Return true if page is private; false otherwise.
	 */
	protected function is_private( string $url ) : bool {
		$private_post_urls = [];
		$query             = new \WP_Query( [ 'post_status' => 'private' ] );

		if ( empty( $query ) ) {
			return false;
		}

		foreach ( $query->posts as $post ) {
			$private_post_urls[] = get_permalink( $post->ID );
		}

		return in_array( $url, $private_post_urls, true );
	}
}
