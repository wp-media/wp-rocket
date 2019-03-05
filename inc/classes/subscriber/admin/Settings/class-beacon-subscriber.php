<?php
namespace WP_Rocket\Subscriber\Admin\Settings;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Settings\Beacon;

/**
 * Beacon Subscriber to WordPress
 *
 * @since 3.2
 * @author Remy Perona
 */
class Beacon_Subscriber implements Subscriber_Interface {
	/**
	 * Beacon instance
	 *
	 * @var Beacon
	 */
	private $beacon;

	/**
	 * Constructor
	 *
	 * @param Beacon $beacon Beacon instance.
	 */
	public function __construct( Beacon $beacon ) {
		$this->beacon = $beacon;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'admin_print_footer_scripts-settings_page_wprocket' => 'insert_script',
		];
	}

	/**
	 * Insert HelpScout Beacon script
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function insert_script() {
		echo $this->beacon->insert_script();
	}
}
