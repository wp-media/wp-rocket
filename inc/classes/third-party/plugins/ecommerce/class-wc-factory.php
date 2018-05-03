<?php
namespace WP_Rocket\Third_Party\Plugins\Ecommerce;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * WooCommerce Factory
 *
 * @since 3.1
 * @author Remy Perona
 */
class WC_Factory {
	/**
	 * Returns the same shared instance of the WooCommerce class
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return WooCommerce
	 */
	public static function create() {
		static $class = null;

		if ( null === $class ) {
			$class = new WooCommerce_Compatibility();
		}

		return $class;
	}
}
