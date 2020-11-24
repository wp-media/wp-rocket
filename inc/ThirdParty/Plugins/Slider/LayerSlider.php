<?php

namespace WP_Rocket\ThirdParty\Plugins\Slider;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

/**
 * Subscriber for compatibility with LayerSlider
 *
 * @since  3.8
 */
class LayerSlider implements Subscriber_Interface {
	use ReturnTypesTrait;

	/**
	 * Subscribed events for LayerSlider.
	 *
	 * @since  3.8
	 *
	 * @return array Array of callbacks.
	 */
	public static function get_subscribed_events() {
		if ( ! rocket_get_constant( 'LS_ROOT_FILE', false ) ) {
			return [];
		}

		// Conflict with LayerSlider: don't add width and height attributes on all images.
		return [
			'rocket_specify_image_dimensions' => 'return_false',
		];
	}

}
