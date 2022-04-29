<?php

namespace WP_Rocket\ThirdParty\Plugins\Ecommerce;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with Themify WooCommerce Product Filter.
 *
 * @since  3.11.0.5
 */
class ThemifyWooCommerceProductFilter implements Subscriber_Interface {

	/**
	 * Plguin
	 *
	 * @var string
	 */
	private $plugin = 'themify-wc-product-filter/themify-wc-product-filter.php';

	/**
	 * Subscribed events for Themify WooCommerce Product Filter.
	 *
	 * @since  3.11.0.5
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_exclude_defer_js' => 'exclude_defer_js',
		];
	}

	/**
	 * Exclude jquery
	 *
	 * @since 3.11.0.5
	 *
	 * @param array $exclude_defer_js Files paths to be excluded.
	 * @return array
	 */
	public function exclude_defer_js( array $exclude_defer_js ): array {

		if ( ! is_plugin_active( $this->plugin ) ) {
			return $exclude_defer_js;
		}

		$exclude_defer_js[] = '/wp-includes/js/jquery/jquery.min.js';

		return $exclude_defer_js;
	}
}
