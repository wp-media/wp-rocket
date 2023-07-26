<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Preload;

trait FormatUrlTrait {
	/**
	 * Format URL.
	 *
	 * @param string $url URL.
	 * @param bool   $use_website_trailing Use the website config for trailing slash.
	 * @return string
	 */
	public function format_url( string $url, bool $use_website_trailing = false ): string {
		$queries         = wp_parse_url( $url, PHP_URL_QUERY ) ?: '';
		$queries         = $this->convert_query_to_array( $queries );
		$ignored_queries = apply_filters( 'rocket_cache_ignored_parameters', [] );

		$queries_keys = array_diff( array_keys( $queries ), array_keys( $ignored_queries ) );

		foreach ( array_keys( $queries ) as $key ) {
			if ( in_array( $key, $queries_keys, true ) ) {
				continue;
			}
			unset( $queries[ $key ] );
		}

		ksort( $queries );

		$url = strtok( $url, '?' );

		if ( $use_website_trailing ) {
			$url = user_trailingslashit( $url );
		} else {
			$url = untrailingslashit( $url );
		}

		return add_query_arg( $queries, $url );
	}

	/**
	 * Convert query string to an array with keys and values.
	 *
	 * @param string $query query string.
	 *
	 * @return array|mixed
	 */
	protected function convert_query_to_array( string $query = '' ) {
		if ( empty( $query ) ) {
			return [];
		}
		$query_array = [];
		parse_str( $query, $query_array );

		return $query_array;
	}

	/**
	 * Can URLs with query strings be preloaded
	 *
	 * @return bool
	 */
	public function can_preload_query_strings(): bool {
		/**
		 * Filter to allow query string in preload.
		 *
		 * @param bool $is_allowed True to allow, false otherwise.
		 */
		return (bool) apply_filters( 'rocket_preload_query_string', false );
	}
}
