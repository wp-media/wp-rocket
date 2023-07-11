<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Front;

class FileResolver {

	/**
	 * Resolves the name from the file from its URL.
	 *
	 * @param string $url URL from the file to resolve.
	 * @return string
	 */
	public function resolve( string $url ): string {
		$parsed_url       = wp_parse_url( $url );
		$home_parsed_url  = wp_parse_url( site_url(), PHP_URL_HOST );
		$home_parsed_path = wp_parse_url( site_url(), PHP_URL_PATH );
		$host_url         = key_exists( 'host', $parsed_url ) ? $parsed_url['host'] : $home_parsed_url;

		if ( $host_url !== $home_parsed_url || ! key_exists( 'path', $parsed_url ) ) {
			return '';
		}

		$path = $parsed_url['path'];

		if ( $home_parsed_path ) {
			$path = str_replace( $home_parsed_path, '', $path );
		}

		return rocket_get_constant( 'ABSPATH', '' ) . $path;
	}
}
