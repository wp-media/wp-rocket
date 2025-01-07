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
	 * Notices instance
	 *
	 * @var Notices
	 */
	private $notices;

	/**
	 * Constructor
	 *
	 * @param AdminBar $admin_bar AdminBar instance.
	 * @param Clean    $clean Clean instance.
	 * @param Notices  $notices Notices instance.
	 */
	public function __construct( $admin_bar, $clean, $notices ) {
		$this->admin_bar = $admin_bar;
		$this->clean     = $clean;
		$this->notices   = $notices;
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
			'admin_notices'                    => [
				[ 'clean_saas_result' ],
				[ 'display_processing_notice' ],
				[ 'display_success_notice' ],
				[ 'display_wrong_license_notice' ],
				[ 'display_saas_error_notice' ],
			],
			'rocket_localize_admin_script'     => 'add_localize_script_data',
			'rocket_dashboard_actions'         => 'display_dashboard_button',
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

	/**
	 * Show admin notice after clearing SaaS tables.
	 *
	 * @return void
	 */
	public function clean_saas_result() {
		$this->notices->clean_saas_result();
	}

	/**
	 * Displays the SaaS currently processing notice
	 *
	 * @return void
	 */
	public function display_processing_notice() {
		$this->notices->display_processing_notice();
	}

	/**
	 * Displays the SaaS success notice
	 *
	 * @return void
	 */
	public function display_success_notice() {
		$this->notices->display_success_notice();
	}

	/**
	 * Display a notification on wrong license.
	 *
	 * @return void
	 */
	public function display_wrong_license_notice() {
		$this->notices->display_wrong_license_notice();
	}

	/**
	 * Display an error notice when the connection to the server fails
	 *
	 * @return void
	 */
	public function display_saas_error_notice() {
		$this->notices->display_saas_error_notice();
	}

	/**
	 * Adds the notice end time to WP Rocket localize script data
	 *
	 * @param array $data Localize script data.
	 *
	 * @return array
	 */
	public function add_localize_script_data( $data ): array {
		return $this->notices->add_localize_script_data( $data );
	}

	/**
	 * Display the dashboard button to clean SaaS features
	 *
	 * @return void
	 */
	public function display_dashboard_button() {
		$this->admin_bar->display_dashboard_button();
	}
}
