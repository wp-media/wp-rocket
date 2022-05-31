<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\Minify;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class AdminSubscriber implements Subscriber_Interface {
	/**
	 * WP Rocket Options
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Instantiate the class
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
	 * Delete all minified cache files
	 *
	 * @return void
	 */
	public function clean_minify_all() {
		// Bail out if minify_js or minify_css is not enabled.
		if (
			! (bool) $this->options->get( 'minify_js', 0 )
			&&
			! (bool) $this->options->get( 'minify_css', 0 )
		) {
			return;
		}

		// Delete all minify cache files.
		rocket_clean_minify();
	}
}
