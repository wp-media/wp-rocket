<?php

namespace WP_Rocket\Engine\Preload\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface
{
	/**
	 * @var Settings
	 */
	protected $settings;

	/**
	 * @param Settings $settings
	 */
	public function __construct(Settings $settings)
	{
		$this->settings = $settings;
	}


	public static function get_subscribed_events()
	{
		return [
			'admin_notices' => [ 'maybe_display_preload_notice' ],
		];
	}

	public function maybe_display_preload_notice() {
		$this->settings->maybe_display_preload_notice();
	}
}
