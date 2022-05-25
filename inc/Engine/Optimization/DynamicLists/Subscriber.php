<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Optimization\DynamicLists;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * DynamicLists instance
	 *
	 * @var DynamicLists
	 */
	private $dynamic_lists;

	/**
	 * Instantiate the class
	 *
	 * @param DynamicLists $dynamic_lists Rest instance.
	 */
	public function __construct( DynamicLists $dynamic_lists ) {
		$this->dynamic_lists = $dynamic_lists;
	}

	/**
	 * Events this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rest_api_init' => 'register_rest_route',
			'admin_print_styles-settings_page_' . WP_ROCKET_PLUGIN_SLUG => ['add_dynamic_lists_script',11],
		];
	}

	/**
	 * Registers the rest support route
	 *
	 * @return void
	 */
	public function register_rest_route() {
		$this->dynamic_lists->register_rest_route();
	}

	public function add_dynamic_lists_script(){
		wp_localize_script(
			'wpr-admin',
			'rocket_dynamic_lists',
			[
				'rest_url'              => rest_url( "wp-rocket/v1/wpr-dynamic-lists/" ),
				'rest_nonce'            => wp_create_nonce( 'wp_rest' ),
			]
		);
	}
}
