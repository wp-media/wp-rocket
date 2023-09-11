<?php
namespace WP_Rocket\ThirdParty\Themes;

class Shoptimizer extends ThirdpartyTheme {

	/**
	 * Theme name
	 *
	 * @var string
	 */
	protected static $theme_name = 'shoptimizer';

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * The array key is the event name. The value can be:
	 *
	 *  * The method name
	 *  * An array with the method name and priority
	 *  * An array with the method name, priority and number of accepted arguments
	 *
	 * For instance:
	 *
	 *  * array('hook_name' => 'method_name')
	 *  * array('hook_name' => array('method_name', $priority))
	 *  * array('hook_name' => array('method_name', $priority, $accepted_args))
	 *  * array('hook_name' => array(array('method_name_1', $priority_1, $accepted_args_1)), array('method_name_2', $priority_2, $accepted_args_2)))
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! self::is_current_theme() ) {
			return [];
		}

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
