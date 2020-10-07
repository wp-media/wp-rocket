<?php

namespace WP_Rocket\Engine\License;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Upgrade instance
	 *
	 * @var Upgrade
	 */
	private $upgrade;

	/**
	 * Instantiate the class
	 *
	 * @param Upgrade $upgrade Upgrade instance.
	 */
	public function __construct( Upgrade $upgrade ) {
		$this->upgrade = $upgrade;
	}

	/**
	 * Events this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_dashboard_license_info' => 'display_upgrade_section',
			'rocket_settings_page_footer'   => 'display_upgrade_popin',
		];
	}

	/**
	 * Displays the upgrade section in the license info block
	 *
	 * @return void
	 */
	public function display_upgrade_section() {
		$this->upgrade->display_upgrade_section();
	}

	/**
	 * Displays the upgrade popin
	 *
	 * @return void
	 */
	public function display_upgrade_popin() {
		$this->upgrade->display_upgrade_popin();
	}
}
