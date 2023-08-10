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

		$path = rocket_url_to_path( strtok( $url, '?' ) );

		if ( ! $path ) {
			return '';
		}

		return $path;
	}
}
