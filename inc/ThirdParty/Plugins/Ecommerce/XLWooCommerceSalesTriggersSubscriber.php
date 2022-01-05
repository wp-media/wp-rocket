<?php

declare( strict_types=1 );

namespace WP_Rocket\ThirdParty\Plugins\Ecommerce;

use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Event_Management\Subscriber_Interface;

class XLWooCommerceSalesTriggersSubscriber implements Subscriber_Interface {

	/**
	 * Delay JS HTML class.
	 *
	 * @var HTML
	 */
	private $delayjs_html;

	/**
	 * WooCommerceSubscriber constructor.
	 *
	 * @param HTML $delayjs_html DelayJS HTML class.
	 */
	public function __construct( HTML $delayjs_html ) {
		$this->delayjs_html = $delayjs_html;
	}

	/**
	 * Get the subscribed events array.
	 *
	 * @return array|array[]
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_delay_js_exclusions' => [ 'modify_delayjs_exclusions_on_product_pages', 10, 2 ]
		];
	}

	/**
	 * Modify delay JS excluded scripts for compatibility.
	 */
	public function modify_delayjs_exclusions_on_product_pages( array $exclusions ) {
		if ( empty( rocket_get_constant('WCST_VERSION' ) ) ) {
			return $exclusions;
		}

		if ( 'product' !== get_post_type() || ! is_single() ) {
			return $exclusions;
		}

		return array_unique(
			array_merge(
				$exclusions,
				[
					'xl-woocommerce-sales-triggers/assets/js/wcst_combined.min.js',
					'/jquery-?[0-9.](.*)(.min|.slim|.slim.min)?.js',
					'js-(before|after)',
					'(?:/wp-content/|/wp-includes/)(.*)'
				]
			)
		);
	}
}
