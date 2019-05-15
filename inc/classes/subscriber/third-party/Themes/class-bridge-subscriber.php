<?php
namespace WP_Rocket\Subscriber\Third_Party\Themes;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Compatibility class for Bridge theme
 *
 * @since 3.3.1
 * @author Remy Perona
 */
class Bridge_Subscriber implements Subscriber_Interface {
	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		$current_theme = wp_get_theme();

		if ( 'Bridge' !== $current_theme->get( 'Name' ) ) {
			return [];
		}

		return [
			'rocket_lazyload_background_images' => 'disable_lazyload_background_images',
		];
	}

	/**
	 * Disable lazyload for background images when using Bridge theme
	 *
	 * @since 3.3.1
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	public function disable_lazyload_background_images() {
		return false;
	}
}
