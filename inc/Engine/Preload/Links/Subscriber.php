<?php

namespace WP_Rocket\Engine\Preload\Links;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Options Data instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Instantiate the class
	 *
	 * @param Options_Data $options Options Data instance.
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
			'wp_enqueue_scripts' => 'add_preload_script',
		];
	}

	/**
	 * Adds the inline script to the footer when the option is enabled
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	public function add_preload_script() {
		if ( ! (bool) $this->options->get( 'preload_links', 0 ) ) {
			return;
		}

		// Register handle with no src to add the inline script after.
		wp_register_script( 'rocket-preload-links', '', [], false, true );
		wp_enqueue_script( 'rocket-preload-links' );
		wp_add_inline_script( 'rocket-preload-links', 'script' );
	}
}
