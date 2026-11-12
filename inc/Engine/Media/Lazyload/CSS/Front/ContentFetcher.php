<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Front;

use WP_Filesystem_Direct;
use WP_Rocket\Engine\Optimization\CSSTrait;
use WP_Rocket\Engine\Optimization\RemoteAssetTrait;

class ContentFetcher {

	use CSSTrait;
	use RemoteAssetTrait;

	/**
	 * WordPress filesystem.
	 *
	 * @var WP_Filesystem_Direct
	 */
	protected $filesystem;

	/**
	 * Instance.
	 *
	 * @param WP_Filesystem_Direct|null $filesystem WordPress filesystem.
	 */
	public function __construct( ?WP_Filesystem_Direct $filesystem = null ) {
		$this->filesystem = $filesystem ?: rocket_direct_filesystem();
	}

	/**
	 * Fetch content from the resource.
	 *
	 * @param string $path Path from the resource.
	 * @param string $destination Destination path.
	 * @return false|string
	 */
	public function fetch( string $path, string $destination ) {
		$content = $this->fetch_content( $path );

		if ( ! $content ) {
			return false;
		}

		$content = $this->move( $this->get_converter( $path, $destination ), $content, $path );

		$this->set_cached_import( $path );

		$content = $this->combine_imports( $content, $destination );

		return $content;
	}

	/**
	 * Fetch the content from the file.
	 *
	 * @param string $path Path to fetch.
	 *
	 * @return false|string
	 */
	protected function fetch_content( string $path ) {
		if ( ! wp_http_validate_url( $path ) ) {
			$file_extension = pathinfo( $path, PATHINFO_EXTENSION );

			if ( strtolower( $file_extension ) !== 'php' ) {
				return $this->filesystem->get_contents( $path );
			}
		}
		return $this->get_remote_asset_content( $path );
	}
}
