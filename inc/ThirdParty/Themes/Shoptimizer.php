<?php
namespace WP_Rocket\ThirdParty\Themes;

class Shoptimizer {
	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_exclude_defer_js' => 'exclude_jquery_deferjs_with_cart_drawer',
		];
	}

	/**
	 * Exclude Jquery from defer JS.
	 *
	 * @param array $exclusions Excluded values from defer JS.
	 *
	 * @return array
	 */
	public function exclude_jquery_deferjs_with_cart_drawer( $exclusions ) {
		if ( ! function_exists( 'shoptimizer_get_option' ) || ! shoptimizer_get_option( 'shoptimizer_layout_woocommerce_enable_sidebar_cart' ) ) {
			return $exclusions;
		}

		$exclusions[] = '\/jquery(-migrate)?-?([0-9.]+)?(.min|.slim|.slim.min)?.js(\?(.*))?';
		return $exclusions;
	}
}
