<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Front;

use WP_Filesystem_Direct;

class ContentFetcher {


	/**
	 * WordPress filesystem.
	 *
	 * @var WP_Filesystem_Direct
	 */
	protected $filesystem;

	/**
	 * Instance.
	 *
	 * @param WP_Filesystem_Direct $filesystem WordPress filesystem.
	 */
	public function __construct( WP_Filesystem_Direct $filesystem = null ) {
		$this->filesystem = $filesystem ?: rocket_direct_filesystem();
	}

	/**
	 * Fetch content from the resource.
	 *
	 * @param string $path Path from the resource.
	 *
	 * @return false|string
	 */
	public function fetch( string $path ) {

		if ( ! wp_http_validate_url( $path ) ) {
			return $this->filesystem->get_contents( $path );
		}

		$content = wp_remote_retrieve_body( wp_remote_get( $path ) );

		if ( ! $content ) {
			return false;
		}

		return $content;
	}
}
