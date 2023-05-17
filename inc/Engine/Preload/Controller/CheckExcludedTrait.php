<?php

namespace WP_Rocket\Engine\Preload\Controller;

use WP_Rocket\Engine\Preload\FormatUrlTrait;

trait CheckExcludedTrait {
	use FormatUrlTrait;

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
		/**
		 * Regex to exclude URI from preload without sanitize.
		 *
		 * @param string[] regexes to check
		 */
		$regexes = (array) apply_filters( 'rocket_preload_exclude_urls_regexes', [] );
		/**
		 * Regex to exclude URI from preload.
		 *
		 * @param string[] regexes to check
		 */
		$regexes_urls = (array) apply_filters( 'rocket_preload_exclude_urls', [] );

		if ( $this->is_match( $url, $regexes, false ) ) {
			return true;
		}

		return $this->is_match( $url, $regexes_urls );
	}

	/**
	 * Check if one of the regexes is matching the URL.
	 *
	 * @param string $url URL to test.
	 * @param array  $regexes Regexes to try.
	 * @param bool   $sanitize sanitize regexes.
	 *
	 * @return bool
	 */
	protected function is_match( string $url, array $regexes, bool $sanitize = true ): bool {
		if ( empty( $regexes ) ) {
			return false;
		}

		$regexes = array_unique( $regexes );
		$url     = $this->format_url( $url );
		$url     = user_trailingslashit( $url );

		foreach ( $regexes as $regex ) {
			if ( ! is_string( $regex ) ) {
				continue;
			}

			if ( $sanitize ) {
				$regex = $this->format_url( $regex );
				$regex = str_replace( '?', '\\?', $regex );
			}

			if ( preg_match( "@$regex@m", $url ) ) {
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
		$queries = wp_parse_url( $url, PHP_URL_QUERY );

		if ( ! $queries ) {
			return false;
		}

		$query_array = [];

		parse_str( $queries, $query_array );

		$excluded_queries = rocket_get_ignored_parameters();

		return count( array_intersect( array_keys( $query_array ), array_keys( $excluded_queries ) ) ) > 0 || $this->is_excluded_by_filter( $url );
	}

	/**
	 * Check if the URL has query string.
	 *
	 * @param string $url URL to check.
	 *
	 * @return bool
	 */
	public function has_query_string( string $url ) {
		$queries = wp_parse_url( $url, PHP_URL_QUERY ) ?: '';

		if ( ! $queries ) {
			return false;
		}

		$query_array = [];

		parse_str( $queries, $query_array );

		$query_array = $this->drop_excluded_params( $query_array, true );

		return ! empty( $query_array );
	}

	/**
	 * Check if the url has query params.
	 *
	 * @param string $url url to check.
	 * @return bool
	 */
	public function has_cached_query_string( string $url ) {
		$queries = wp_parse_url( $url, PHP_URL_QUERY ) ?: '';

		if ( empty( $queries ) ) {
			return true;
		}

		$queries = $this->convert_query_to_array( $queries );

		$queries = $this->drop_excluded_params( $queries );

		$cache_query_string = get_rocket_cache_query_string();

		$default_params = [
			'lang',
			'permalink_name',
			'lp-variation-id',
		];

		/**
		 * At this point we’re in the WP’s search page.
		 * This filter allows to cache search results.
		 *
		 * @since 2.3.8
		 *
		 * @param bool $cache_search True will force caching search results.
		 */
		if ( apply_filters( 'rocket_cache_search', false ) ) {
			$default_params [] = 's';
		}

		$cache_query_string = array_merge(
			$cache_query_string,
			$default_params
		);

		if ( ! $cache_query_string ) {
			return count( $queries ) === 0;
		}

		return empty( $queries ) || count( array_intersect( array_keys( $queries ), $cache_query_string ) ) > 0;
	}

	/**
	 * Drop excluded query params.
	 *
	 * @param array $queries queries from the url.
	 * @param bool  $only_keys has the query array only keys.
	 *
	 * @return array|int[]|string[]
	 */
	protected function drop_excluded_params( array $queries, bool $only_keys = false ) {
		$ignored_queries = apply_filters( 'rocket_cache_ignored_parameters', [] );
		$queries_keys    = $only_keys ? $queries : array_keys( $queries );
		$queries_keys    = array_diff( $queries_keys, array_keys( $ignored_queries ) );

		if ( $only_keys ) {
			return $queries_keys;
		}

		foreach ( array_keys( $queries ) as $key ) {
			if ( in_array( $key, $queries_keys, true ) ) {
				continue;
			}
			unset( $queries[ $key ] );
		}

		return $queries;
	}
}
