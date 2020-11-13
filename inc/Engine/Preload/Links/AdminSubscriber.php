<?php

namespace WP_Rocket\Engine\Preload\Links;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class AdminSubscriber implements Subscriber_Interface {
	/**
	 * Options Data instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Instantiate the class.
	 *
	 * @param Options_Data $options Options Data instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_first_install_options' => 'add_option',
			'rocket_plugins_to_deactivate' => 'add_incompatible_plugins',
		];
	}

	/**
	 * Adds the option key & value to the WP Rocket options array
	 *
	 * @since 3.7
	 *
	 * @param array $options WP Rocket options array.
	 * @return array
	 */
	public function add_option( $options ) {
		$options = (array) $options;

		$options['preload_links'] = 1;

		return $options;
	}

	/**
	 * Adds plugins incompatible with preload links to our notice.
	 *
	 * @since 3.7
	 *
	 * @param array $plugins Array of incompatible plugins.
	 * @return array
	 */
	public function add_incompatible_plugins( $plugins ) {
		if ( ! (bool) $this->options->get( 'preload_links', 0 ) ) {
			return $plugins;
		}

		$plugins['flying-pages'] = 'flying-pages/flying-pages.php';
		$plugins['instant-page'] = 'instant-page/instantpage.php';
		$plugins['quicklink']    = 'quicklink/quicklink.php';

		return $plugins;
	}
}
