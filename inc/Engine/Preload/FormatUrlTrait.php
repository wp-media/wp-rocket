<?php

namespace WP_Rocket\Engine\Preload;

trait FormatUrlTrait {

	/**
	 * Format URL.
	 *
	 * @param string $url URL.
	 *
	 * @return string
	 */
	public function format_url( string $url ) {
		$queries = wp_parse_url( $url, PHP_URL_QUERY ) ?: '';
		$queries = $this->convert_query_to_array( $queries );

		ksort( $queries );
		$url = strtok( $url, '?' );
		return add_query_arg( $queries, $url );
	}

	/**
	 * Convert query string to an array with keys and values.
	 *
	 * @param string $query query string.
	 * @return array|mixed
	 */
	protected function convert_query_to_array( string $query ) {

		if ( ! $query ) {
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
}
