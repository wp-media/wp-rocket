<?php

namespace WP_Rocket\Engine\Media\Images;

use WP_Rocket\Admin\Options_Data;
use WP_Filesystem_Direct;

/**
 * Images Frontend Class
 *
 * @since 3.8
 */
class Frontend {

	/**
	 * Options_Data instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Cache local paths for images.
	 *
	 * @var array
	 */
	private $local_paths = [];

	/**
	 * Filesystem instance
	 *
	 * @var WP_Filesystem_Direct
	 */
	private $filesystem;

	/**
	 * Frontend constructor.
	 *
	 * @param Options_Data $options Options_Data instance.
	 * @param WP_Filesystem_Direct $filesystem Filesystem instance.
	 */
	public function __construct( Options_Data $options, $filesystem = null ) {
		$this->options = $options;

		if ( null === $filesystem ) {
			$filesystem = rocket_direct_filesystem();
		}

		$this->filesystem = $filesystem;
	}

	/**
	 * Specify image dimensions and insert it into images.
	 *
	 * @param string $html Buffer Page HTML contents.
	 *
	 * @return string Buffer Page HTML contents after inserting dimentions into images.
	 */
	public function specify_image_dimensions( $html ) {
		if ( ! $this->options->get( 'image_dimensions', false ) ) {
			return $html;
		}

		/**
		 * Filter images dimensions attributes
		 *
		 * @since 2.2
		 *
		 * @param bool Do the job or not.
		 */
		if ( ! apply_filters( 'rocket_specify_image_dimensions', true ) ) {
			return $html;
		}

		// Get all images without width or height attribute.
		preg_match_all( '/<img(?:[^>](?!(height|width)=[\'\"](?:\S+)[\'\"]))*+>/i', $html, $images_match );

		if ( empty( $images_match ) ) {
			return $html;
		}

		$replaces = [];

		foreach ( $images_match[0] as $image ) {

			// Don't touch lazy-load file (no conflict with Photon (Jetpack)).
			if (
				false !== strpos( $image, 'data-lazy-original' )
				||
				false !== strpos( $image, 'data-no-image-dimensions' )
			) {
				continue;
			}

			// Get link of the file.
			if ( ! preg_match( '/src\s*=\s*[\'"]([^\'"]+)/i', $image, $src_match ) ) {
				continue;
			}

			$image_url = $src_match[1];

			if ( $this->is_external_file( $image_url ) ) {
				if ( ! $this->can_specify_dimensions_external_images() ) {
					continue;
				}

				if ( ! $this->image_exists( $image_url, true ) ) {
					continue;
				}

				$sizes = getimagesize( $image_url );
			} else {
				$local_path = $this->get_local_path( $image_url );

				if ( ! $this->image_exists( $local_path, false ) ) {
					continue;
				}

				$sizes = getimagesize( $this->get_local_path( $image_url ) );
			}

			if ( ! $sizes ) {
				continue;
			}

			// Add width and height attribute.
			$image = str_replace( '<img', '<img ' . $sizes[3], $image );

			// Replace image with new attributes, we will replace all images at once after the loop for optimizations.
			$replaces[ $image ] = $this->assign_width_height( $image, $sizes[3] );
		}

		if ( empty( $replaces ) ) {
			return $html;
		}

		return str_replace( array_keys( $replaces ), $replaces, $image );
	}

	/**
	 * Determines if the file is external.
	 *
	 * @since 3.8
	 *
	 * @param string $url URL of the file.
	 * @return bool True if external, false otherwise.
	 */
	private function is_external_file( $url ) {
		$file = get_rocket_parse_url( $url );

		if ( empty( $file['path'] ) ) {
			return true;
		}

		$wp_content = wp_parse_url( content_url() );

		if ( empty( $wp_content['host'] ) || empty( $wp_content['path'] ) ) {
			return true;
		}

		/**
		 * Filters the allowed hosts for optimization
		 *
		 * @since  3.4
		 *
		 * @param array $hosts Allowed hosts.
		 * @param array $zones Zones to check available hosts.
		 */
		$hosts   = (array) apply_filters( 'rocket_cdn_hosts', [], [ 'all', 'css_and_js', '' ] );
		$hosts[] = $wp_content['host'];
		$langs   = get_rocket_i18n_uri();

		// Get host for all langs.
		foreach ( $langs as $lang ) {
			$url_host = wp_parse_url( $lang, PHP_URL_HOST );

			if ( ! isset( $url_host ) ) {
				continue;
			}

			$hosts[] = $url_host;
		}

		$hosts = array_unique( $hosts );

		if ( empty( $hosts ) ) {
			return true;
		}

		// URL has domain and domain is part of the internal domains.
		if ( ! empty( $file['host'] ) ) {
			foreach ( $hosts as $host ) {
				if ( false !== strpos( $url, $host ) ) {
					$this->local_paths[ md5( $url ) ] = str_replace( $host, WP_CONTENT_DIR, $url );
					return false;
				}
			}

			return true;
		}

		// URL has no domain and doesn't contain the WP_CONTENT path or wp-includes.
		return ! preg_match( '#(' . $wp_content['path'] . '|wp-includes)#', $file['path'] );
	}

	/**
	 * Get local absolute path for image.
	 *
	 * @param string $url Image url.
	 *
	 * @return string Image absolute local path.
	 */
	private function get_local_path( $url ) {
		if ( isset( $this->local_paths[ md5( $url ) ] ) ) {
			return $this->local_paths[ md5( $url ) ];
		}

		return str_replace( content_url(), WP_CONTENT_DIR, $url );
	}

	/**
	 * Check if we can specify dimensions for external images.
	 *
	 * @return bool Valid to be parsed or not.
	 */
	private function can_specify_dimensions_external_images() {
		return ini_get( 'allow_url_fopen' ) && apply_filters( 'rocket_specify_image_dimensions_for_distant', false );
	}

	/**
	 * Assign width and height attributes to the img tag.
	 *
	 * @param string $image IMG tag.
	 * @param string $width_height Width/Height attributes in ready state like [height="100" width="100"].
	 *
	 * @return string IMG tag after adding attributes otherwise return the input img when error.
	 */
	private function assign_width_height( string $image, $width_height ) {
		// Remove old width and height attributes if found.
		$changed_image = preg_replace( '/(height|width)=[\'"](?:\S+)*[\'"]/i', '', $image );
		$changed_image = preg_replace( '<\s*img', '<img ' . $width_height, $changed_image );

		if ( null === $changed_image ) {
			return $image;
		}

		return $changed_image;
	}

	/**
	 * Check if the image exists, internal or external image.
	 *
	 * @param string $image    Image Url for external and Image absolute path for internal.
	 * @param bool   $external If this image is external or not.
	 *
	 * @return bool If image exists or not.
	 */
	private function image_exists( $image, $external = false ) {
		if ( ! $external ) {
			return $this->filesystem->exists( $image );
		}

		$file_headers = get_headers( $image );
		if ( ! $file_headers ) {
			return false;
		}

		return strpos( $file_headers[0], '404' ) === false;
	}

}
