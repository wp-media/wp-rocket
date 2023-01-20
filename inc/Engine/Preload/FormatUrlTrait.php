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
		$queries = wp_parse_url( $url, PHP_URL_QUERY ) ?: '';
		$queries = $this->convert_query_to_array( $queries );

		ksort( $queries );

		$url = strtok( $url, '?' );

		if ( $use_website_trailing ) {
			$url = trailingslashit( $url );
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

		$query = trim( $query, '&' );

		return array_reduce(
			explode( '&', $query ),
			static function( $result, $query ) {
				$param = explode( '=', $query );

				if ( count( $param ) < 2 ) {
					return $result;
				}

				$result[ $param[0] ] = $param[1];
				return $result;
			},
			[]
			);
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
