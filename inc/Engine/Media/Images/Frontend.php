<?php

namespace WP_Rocket\Engine\Media\Images;

use WP_Rocket\Admin\Options_Data;

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

	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

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
		if ( ! apply_filters( 'rocket_specify_image_dimensions', false ) ) {
			return $html;
		}

		// Get all images without width or height attribute.
		preg_match_all( '/<img(?:[^>](?!(height|width)=[\'\"](?:\S+)[\'\"]))*+>/i', $html, $images_match );

		if ( empty( $images_match ) ) {
			return $html;
		}

		foreach ( $images_match[0] as $image ) {

			// Don't touch lazy-load file (no conflict with Photon (Jetpack)).
			if (
				false !== strpos( $image, 'data-lazy-original' )
				||
				false !== strpos( $image, 'data-no-image-dimensions' )
			) {
				continue;
			}

			$tmp = $image;

			// Get link of the file.
			preg_match( '/src\s*=\s*[\'"]([^\'"]+)/i', $image, $src_match );

			if ( $this->is_external_file( $src_match[1] ) ) {

			} else {

			}

			// Get infos of the URL.
			$image_url = wp_parse_url( $src_match[1] );

			// Check if the link isn't external.
			if ( empty( $image_url['host'] ) || rocket_remove_url_protocol( home_url() ) === $image_url['host'] ) {
				// Get image attributes.
				$sizes = getimagesize( ABSPATH . $image_url['path'] );
			} else {
				/**
				 * Filter distant images dimensions attributes
				 *
				 * @since 2.2
				 *
				 * @param bool Do the job or not
				 */
				if ( ini_get( 'allow_url_fopen' ) && apply_filters( 'rocket_specify_image_dimensions_for_distant', false ) ) {
					// Get image attributes.
					$sizes = getimagesize( $image_url['scheme'] . '://' . $image_url['host'] . $image_url['path'] );
				}
			}

			if ( ! empty( $sizes ) ) {
				// Add width and height attribute.
				$image = str_replace( '<img', '<img ' . $sizes[3], $image );

				// Replace image with new attributes.
				$buffer = str_replace( $tmp, $image, $buffer );
			}
		}

		return $buffer;
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
		$hosts   = (array) apply_filters( 'rocket_cdn_hosts', [], [ 'all', 'css_and_js', self::FILE_TYPE ] );
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
					return false;
				}
			}

			return true;
		}

		// URL has no domain and doesn't contain the WP_CONTENT path or wp-includes.
		return ! preg_match( '#(' . $wp_content['path'] . '|wp-includes)#', $file['path'] );
	}

}
