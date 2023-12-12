<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Saas\Admin;

use WP_Admin_Bar;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * AdminBar instance
	 *
	 * @var AdminBar
	 */
	private $admin_bar;

	/**
	 * Constructor
	 *
	 * @param AdminBar $admin_bar AdminBar instance.
	 */
	public function __construct( $admin_bar ) {
		$this->admin_bar = $admin_bar;
	}

	/**
	 * Array of events this subscriber listens to
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_admin_bar_items' => [
				[ 'add_clean_saas_menu_item' ],
				[ 'add_clean_url_menu_item' ],
			],
		];
	}

	/**
	 * Add clean SaaS data to WP Rocket admin bar menu
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @return void
	 */
	public function add_clean_saas_menu_item( WP_Admin_Bar $wp_admin_bar ) {
		$this->admin_bar->add_clean_saas_menu_item( $wp_admin_bar );
	}

	/**
	 * Add clean SaaS URL data to WP Rocket admin bar menu
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @return void
	 */
	public function add_clean_url_menu_item( WP_Admin_Bar $wp_admin_bar ) {
		$this->admin_bar->add_clean_url_menu_item( $wp_admin_bar );
	}
}
