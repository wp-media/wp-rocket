<?php

namespace WP_Rocket\Engine\Media\ImageDimensions;

use WP_Filesystem_Direct;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Settings\Settings;
use WP_Rocket\Logger\Logger;

class ImageDimensions {
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
	 * @param Options_Data         $options Options_Data instance.
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
	 * Adds the images dimensions option to WP Rocket options array
	 *
	 * @since 3.8
	 *
	 * @param array $options WP Rocket options array.
	 * @return array
	 */
	public function add_option( array $options ) : array {
		$options['image_dimensions'] = 0;

		return $options;
	}

	/**
	 * Sanitizes the option value when saving from the settings page
	 *
	 * @since 3.8
	 *
	 * @param array    $input    Array of sanitized values after being submitted by the form.
	 * @param Settings $settings Settings class instance.
	 * @return array
	 */
	public function sanitize_option_value( array $input, Settings $settings ) : array {
		$input['image_dimensions'] = $settings->sanitize_checkbox( $input, 'image_dimensions' );

		return $input;
	}

	/**
	 * Specify image dimensions and insert it into images.
	 *
	 * @param string $html Buffer Page HTML contents.
	 *
	 * @return string Buffer Page HTML contents after inserting dimentions into images.
	 */
	public function specify_image_dimensions( $html ) {
		Logger::debug( 'Start Specify Image Dimensions.' );
		if ( ! $this->can_specify_dimensions_images() ) {
			Logger::debug( 'Specify Image Dimensions failed because option is not enabled from admin or by filter (rocket_specify_image_dimensions).' );
			return $html;
		}

		// Get all images without width or height attribute.
		$images_regex = '<img(?:[^>](?!(height|width)=[\'\"](?:\S+)[\'\"]))*+>';

		/**
		 * Filters Specify image dimensions inside picture tags also.
		 *
		 * @since  3.8
		 *
		 * @param bool Do or not, Default is True so it will skip all img tags that are inside picture tag.
		 */
		if ( apply_filters( 'rocket_specify_dimension_skip_pictures', true ) ) {
			$images_regex = '<\s*picture[^>]*>.*' . $images_regex . '.*<\s*\/\s*picture\s*>(*SKIP)(*FAIL)|' . $images_regex;
		}
		preg_match_all( "/{$images_regex}/is", $html, $images_match );

		if ( empty( $images_match ) ) {
			Logger::debug( 'Specify Image Dimensions failed because there is no image without dimensions on this page.' );
			return $html;
		}

		$replaces = [];
		/**
		 * Filters Page images passed to specify dimensions.
		 *
		 * @since  3.8
		 *
		 * @param array Page images.
		 */
		$images = apply_filters( 'rocket_specify_dimension_images', $images_match[0] );

		Logger::debug( 'Specify Image Dimensions found ( ' . count( $images ) . ' ).', $images );

		foreach ( $images as $image ) {

			$image_url = $this->can_specify_dimensions_one_image( $image );

			if ( ! $image_url ) {
				Logger::debug(
					'Specify Image Dimensions failed because it has attribute (data-lazy-original or data-no-image-dimensions) or it\'s without src.',
					[ 'image' => $image ]
				);
				continue;
			}

			$sizes = $this->get_image_sizes( $image_url );

			if ( ! $sizes ) {
				continue;
			}

			// Replace image with new attributes, we will replace all images at once after the loop for optimizations.
			$replaces[ $image ] = $this->assign_width_height( $image, $sizes );
		}

		if ( empty( $replaces ) ) {
			return $html;
		}

		return str_replace( array_keys( $replaces ), $replaces, $html );
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
		$hosts   = (array) apply_filters( 'rocket_cdn_hosts', [], [ 'all' ] );
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
					$this->local_paths[ md5( $url ) ] = str_replace( $host, untrailingslashit( rocket_get_constant( 'ABSPATH' ) ), rocket_remove_url_protocol( $url ) );
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

		return str_replace( content_url(), rocket_get_constant( 'WP_CONTENT_DIR' ), $url );
	}

	/**
	 * Check if we can specify dimensions for external images.
	 *
	 * @return bool Valid to be parsed or not.
	 */
	private function can_specify_dimensions_external_images() {
		/**
		 * Enable/Disable specify image dimensions for external images.
		 *
		 * @since 3.8
		 *
		 * @param bool Specify image dimensions for external images or not.
		 */
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
		$changed_image = preg_replace( '/<\s*img/i', '<img ' . $width_height, $changed_image );

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

		return false !== strstr( $file_headers[0], '200' );
	}

	/**
	 * Check if we can specify image dimensions for all images.
	 *
	 * @return bool Can we or not.
	 */
	private function can_specify_dimensions_images() {
		/**
		 * Filter images dimensions attributes process.
		 *
		 * @since 2.2
		 *
		 * @param bool Do the job or not.
		 */
		return apply_filters( 'rocket_specify_image_dimensions', false )
			||
			$this->options->get( 'image_dimensions', false );
	}

	/**
	 * Check if we can specify image dimensions for one image.
	 *
	 * @param string $image Full img tag.
	 *
	 * @return false|string false if we can't specify for this image otherwise get img src attribute.
	 */
	private function can_specify_dimensions_one_image( $image ) {
		// Don't touch lazy-load file (no conflict with Photon (Jetpack)).
		if (
			false !== strpos( $image, 'data-lazy-original' )
			||
			false !== strpos( $image, 'data-no-image-dimensions' )
			||
			! preg_match( '/\s+src\s*=\s*[\'"](?<url>[^\'"]+)/i', $image, $src_match )
		) {
			return false;
		}

		return $src_match['url'];

	}

	/**
	 * Get Image sizes.
	 *
	 * @param string $image_url Image url to get sizes for.
	 *
	 * @return string|false Get image sizes otherwise false.
	 */
	private function get_image_sizes( string $image_url ) {
		if ( $this->is_external_file( $image_url ) ) {
			if ( ! $this->can_specify_dimensions_external_images() ) {
				Logger::debug(
					'Specify Image Dimensions failed because you/server disabled specifying dimensions for external images.',
					[ 'image_url' => $image_url ]
				);
				return false;
			}

			if ( ! $this->image_exists( $image_url, true ) ) {
				Logger::debug(
					'Specify Image Dimensions failed because external image not found.',
					[ 'image_url' => $image_url ]
				);
				return false;
			}

			$sizes = getimagesize( $image_url );

			if ( ! $sizes ) {
				Logger::debug(
					'Specify Image Dimensions failed because image is not valid.',
					[ 'image_url' => $image_url ]
				);
				return false;
			}

			return $sizes[3];
		}

		$local_path = $this->get_local_path( $image_url );

		if ( ! $this->image_exists( $local_path, false ) ) {
			Logger::debug(
				'Specify Image Dimensions failed because internal image is not found.',
				[ 'image_url' => $image_url ]
			);
			return false;
		}

		$sizes = getimagesize( $this->get_local_path( $image_url ) );

		if ( ! $sizes ) {
			Logger::debug(
				'Specify Image Dimensions failed because image is not valid.',
				[ 'image_url' => $image_url ]
			);

			return false;
		}

		return $sizes[3];
	}
}
