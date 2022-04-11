<?php

namespace WP_Rocket\Engine\Optimization\Minify;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;

/**
 * Minify Admin subscriber
 *
 * @since 3.5.4
 */
class AdminSubscriber implements Subscriber_Interface {
	/**
	 * WP Rocket Options
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Run on new instance
	 *
	 * @param Options_Data $options WP Rocket Options Instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'switch_theme' => 'clean_minify_all',
		];
	}

	/**
	 * Delete all minified cache file
	 *
	 * @return void
	 */
	public function clean_minify_all() {
		// Bail out if minify_js or minify_css is not enabled.
		if ( ! (bool) $this->options->get( 'minify_js' ) && ! (bool) $this->options->get( 'minify_css' ) ) {
			return;
		}
		// Delete all minify cache files.
		rocket_clean_minify();
	}
}
