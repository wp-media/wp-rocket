<?php

namespace WP_Rocket\Engine\License;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	private $upgrade;

	public function __construct( Upgrade $upgrade ) {
		$this->upgrade = $upgrade;
	}

	public static function get_subscribed_events() {
		return [
			'rocket_after_license_info'   => 'display_upgrade_section',
			'rocket_settings_page_footer' => 'display_upgrade_popin',
			'rocket_menu_title'           => 'add_promo_bubble',
			'admin_footer-settings_page_wprocket' => [
				[ 'dismiss_notification_bubble' ],
				[ 'schedule_promo_reset' ],
			],
			'rocket_schedule_promo_reset' => 'reset_promo_user_meta',
		];
	}

	public function display_upgrade_section() {
		$this->upgrade->display_upgrade_section();
	}

	public function display_upgrade_popin() {
		$this->upgrade->display_upgrade_popin();
	}

	public function add_promo_bubble( $menu_title ) {
		return $this->upgrade->add_notification_bubble( $menu_title );
	}

	public function dismiss_notification_bubble() {
		$this->upgrade->dismiss_notification_bubble();
	}

	public function schedule_promo_reset() {
		$this->upgrade->schedule_promo_reset();
	}

	public function reset_promo_user_meta() {
		$this->upgrade->reset_promo_user_meta();
	}
}
