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
	 * CF7 scripts load status.
	 *
	 * @var bool
	 */
	private $load_js;

	/**
	 * Subscribed events.
	 */
	public static function get_subscribed_events() {
		return [
			'template_redirect' => 'maybe_optimize_contact_form_7',
		];
	}

	/**
	 * Optimize ContactForm7 scripts.
	 */
	public function maybe_optimize_contact_form_7() {
		/**
		 * Filters register this compatibility events or not.
		 *
		 * @param bool $status Load the compatibility file or not, default is True.
		 * @param string $thirdparty Thirdparty id.
		 */
		if ( ! apply_filters( 'rocket_thirdparty_load', true, 'contact-form-7' ) ) {
			return;
		}

		// The wpcf7_shortcode_callback action was added in CF7 version 5.8.1.
		if ( ! defined( 'WPCF7_VERSION' ) || version_compare( WPCF7_VERSION, self::REQUIRED_CF7_VERSION, '<' ) ) {
			return;
		}

		// Force scripts and styles to not load by default.
		add_filter( 'wpcf7_load_js', '__return_false' );
		add_filter( 'wpcf7_load_css', '__return_false' );
		$this->load_js = false;

		add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts_fallback' ], PHP_INT_MAX );
		add_action( 'wpcf7_enqueue_scripts', [ $this, 'scripts_loaded' ] );

		// Conditionally enqueue scripts.
		add_action( 'wpcf7_shortcode_callback', [ $this, 'conditionally_enqueue_scripts' ] );
		add_action( 'wpcf7_shortcode_callback',  [ $this, 'conditionally_enqueue_styles' ] );
	}

	/**
	 * Enqueue scripts if not already enqueued.
	 */
	public function conditionally_enqueue_scripts() {
		if ( $this->load_js ) { // Prevent double-enqueueing when multiple forms present.
			return;
		}
		if ( did_action( 'wp_enqueue_scripts' ) ) {
			// @phpstan-ignore-next-line
			wpcf7_enqueue_scripts();
			return;
		}
		add_filter( 'wpcf7_load_js', '__return_true', 11 );
	}

	/**
	 * Enqueue styles if not already enqueued.
	 */
	public function conditionally_enqueue_styles() {
		if ( did_action( 'wpcf7_enqueue_styles' ) ) { // Prevent double-enqueueing when multiple forms present.
			return;
		}
		if ( did_action( 'wp_enqueue_scripts' ) ) {
			// @phpstan-ignore-next-line
			wpcf7_enqueue_styles();
			return;
		}
		add_filter( 'wpcf7_load_css', '__return_true', 11 );
	}

	/**
	 * Load CF7 scripts only when CF7 main script is added as a dependency.
	 *
	 * @return void
	 */
	public function load_scripts_fallback() {
		if ( $this->load_js || ! $this->cf7_script_enqueued_as_dependency() ) {
			return;
		}

		// @phpstan-ignore-next-line
		wpcf7_enqueue_scripts();
	}

	/**
	 * Check if CF7 main script is added as a dependency for any script.
	 *
	 * @return bool
	 */
	private function cf7_script_enqueued_as_dependency() {
		foreach ( wp_scripts()->registered as $script ) {
			foreach ( $script->deps as $dep ) {
				if ( 'contact-form-7' === $dep ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Set a flag that scripts are loaded.
	 *
	 * @return void
	 */
	public function scripts_loaded() {
		$this->load_js = true;
	}
}
