<?php

namespace WP_Rocket\Engine\Preload\Admin;

use stdClass;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Controller\ClearCache;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\Logger;

class Subscriber implements Subscriber_Interface {

	/**
	 * Options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Creates an instance of the class.
	 *
	 * @param Options_Data $options Options instance.
	 * @param Settings     $settings Settings instance.
	 */
	public function __construct( Options_Data $options, Settings $settings ) {
		$this->options  = $options;
		$this->settings = $settings;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices'          => [
				[ 'maybe_display_preload_notice' ],
			],
			'rocket_options_changed' => 'preload_homepage',
		];
	}

	/**
	 * Maybe display the preload notice.
	 *
	 * @return void
	 */
	public function maybe_display_preload_notice() {
		$this->settings->maybe_display_preload_notice();
	}

	/**
	 * Preload the homepage after changing the settings
	 *
	 * @return void
	 */
	public function preload_homepage() {
		$this->settings->preload_homepage();
	}
}
