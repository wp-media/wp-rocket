<?php

namespace WP_Rocket\Engine\Media;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Images Subscriber
 *
 * @since 3.8
 */
class ImagesSubscriber implements Subscriber_Interface {

	/**
	 * Options_Data instance
	 *
	 * @var Options_Data
	 */
	private $options;

	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	public static function get_subscribed_events() {
		return [
			'rocket_buffer' => 'specify_image_dimensions'
		];
	}

	public function specify_image_dimensions( $buffer ) {
		if ( ! $this->options->get( 'image_dimensions', false ) ) {
			return $buffer;
		}

		/**
		 * Filter images dimensions attributes
		 *
		 * @since 2.2
		 *
		 * @param bool Do the job or not.
		 */
		if ( ! apply_filters( 'rocket_specify_image_dimensions', false ) ) {
			return $buffer;
		}

		// Get all images without width or height attribute.
		preg_match_all( '/<img(?:[^>](?!(height|width)=[\'\"](?:\S+)[\'\"]))*+>/i', $buffer, $images_match );

		if ( empty( $images_match ) ) {
			return $buffer;
		}

		foreach ( $images_match[0] as $image ) {

			// Don't touch lazy-load file (no conflit with Photon (Jetpack)).
			if ( strpos( $image, 'data-lazy-original' ) || strpos( $image, 'data-no-image-dimensions' ) ) {
				continue;
			}

			$tmp = $image;

			// Get link of the file.
			preg_match( '/src=[\'"]([^\'"]+)/', $image, $src_match );

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
				// Add width and width attribute.
				$image = str_replace( '<img', '<img ' . $sizes[3], $image );

				// Replace image with new attributes.
				$buffer = str_replace( $tmp, $image, $buffer );
			}
		}

		return $buffer;
	}
}
