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
	 * Clean instance
	 *
	 * @var Clean
	 */
	private $clean;

	/**
	 * Constructor
	 *
	 * @param AdminBar $admin_bar AdminBar instance.
	 * @param Clean    $clean Clean instance.
	 */
	public function __construct( $admin_bar, $clean ) {
		$this->admin_bar = $admin_bar;
		$this->clean     = $clean;
	}

	/**
	 * Array of events this subscriber listens to
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_admin_bar_items'           => [
				[ 'add_clean_saas_menu_item' ],
				[ 'add_clean_url_menu_item' ],
			],
			'admin_post_rocket_clean_saas'     => 'clean_saas',
			'admin_post_rocket_clean_saas_url' => 'clean_url_saas',
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

	/**
	 * Truncate SaaS tables when clicking on the dashboard button
	 *
	 * @return void
	 */
	public function clean_saas() {
		$this->clean->clean_saas();
	}

	/**
	 * Clean SaaS for the current URL.
	 *
	 * @return void
	 */
	public function clean_url_saas() {
		$this->clean->clean_url_saas();
	}
}
