<?php
namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;

class ContactForm7 implements Subscriber_Interface {
	/**
	 * Subscribed events.
	 */
	public static function get_subscribed_events() {
		return [
			'wp' => [ 'maybe_optimize_contact_form_7', 10 ],
		];
	}

	/**
	 * Optimize ContactForm7 scripts.
	 *
	 * @return array
	 */
	public function maybe_optimize_contact_form_7() {
		if ( defined( 'WPCF7_LOAD_JS' ) && WPCF7_LOAD_JS && ! has_action( 'wpcf7_shortcode_callback' ) ) {
			// Only load the frontend scripts on the new 'wpcf7_shortcode_callback' hook.
			add_filter( 'wpcf7_load_js', '__return_false' );
			add_action( 'wpcf7_shortcode_callback', 'wpcf7_enqueue_scripts' );
		}
	}
}
