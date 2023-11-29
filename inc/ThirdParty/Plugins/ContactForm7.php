<?php
namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;

class ContactForm7 implements Subscriber_Interface {

	/**
	 * Required CF6 version.
	 *
	 * Version in which the wpcf7_shortcode_callback action was introduced.
	 *
	 * @var string
	 */
	const REQUIRED_CF7_VERSION = '5.8.1';

	/**
	 * Subscribed events.
	 */
	public static function get_subscribed_events() {
		return [
			'template_redirect' => [ 'maybe_optimize_contact_form_7', 10 ],
		];
	}

	/**
	 * Optimize ContactForm7 scripts.
	 */
	public function maybe_optimize_contact_form_7() {
		// The wpcf7_shortcode_callback action was added in CF7 version 5.8.1.
		if ( ! defined( 'WPCF7_VERSION' ) || version_compare( WPCF7_VERSION, self::REQUIRED_CF7_VERSION, '<' ) ) {
			return;
		}

		// Force scripts and styles to not load by default.
		add_filter( 'wpcf7_load_js', '__return_false', PHP_INT_MAX );
		add_filter( 'wpcf7_load_css', '__return_false', PHP_INT_MAX );

		// Conditionally enqueue scripts.
		add_action( 'wpcf7_shortcode_callback', [ $this, 'conditionally_enqueue_scripts' ] );
		add_action( 'wpcf7_shortcode_callback',  [ $this, 'conditionally_enqueue_styles' ] );
	}

	/**
	 * Enqueue scripts if not already enqueued.
	 */
	public function conditionally_enqueue_scripts() {
		if ( ! did_action( 'wpcf7_enqueue_scripts' ) ) { // Prevent double-enqueueing when multiple forms present.
			wpcf7_enqueue_scripts();
		}
	}

	/**
	 * Enqueue styles if not already enqueued.
	 */
	public function conditionally_enqueue_styles() {
		if ( ! did_action( 'wpcf7_enqueue_styles' ) ) { // Prevent double-enqueueing when multiple forms present.
			wpcf7_enqueue_styles();
		}
	}
}
