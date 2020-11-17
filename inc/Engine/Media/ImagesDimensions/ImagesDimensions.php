<?php

namespace WP_Rocket\Engine\Media\ImagesDimensions;

class ImagesDimensions {
	/**
	 * Adds the images dimensions option to WP Rocket options array
	 *
	 * @param array $options WP Rocket options array.
	 * @return array
	 */
	public function add_option( array $options ) : array {
		$options['images_dimensions'] = 0;

		return $options;
	}
}
