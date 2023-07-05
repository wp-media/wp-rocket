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
		$parsed_url      = wp_parse_url( $url );
		$home_parsed_url = wp_parse_url( home_url(), PHP_URL_HOST );
		$host_url        = key_exists( 'host', $parsed_url ) ? $parsed_url['host'] : $home_parsed_url;

		if ( $host_url !== $home_parsed_url || ! key_exists( 'path', $parsed_url ) ) {
			return '';
		}

		$path = $parsed_url['path'];

		return get_home_path() . $path;
	}
}
