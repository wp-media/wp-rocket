<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Admin;

use WP_Admin_Bar;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Controller instance.
	 *
	 * @var Controller
	 */
	private $controller;

	/**
	 * AdminBar instance.
	 *
	 * @var AdminBar
	 */
	private $admin_bar;

	/**
	 * Clean Instance.
	 *
	 * @var Clean
	 */
	private $clean;

	/**
	 * Notices Instance.
	 *
	 * @var Notices
	 */
	private $notices;

	/**
	 * Instantiate the class
	 *
	 * @param Controller $controller Controller instance.
	 * @param AdminBar   $admin_bar Admin bar instance.
	 * @param Clean      $clean Clean instance.
	 * @param Notices    $notices Notices instance.
	 */
	public function __construct( Controller $controller, AdminBar $admin_bar, Clean $clean, Notices $notices ) {
		$this->controller = $controller;
		$this->admin_bar  = $admin_bar;
		$this->clean      = $clean;
		$this->notices    = $notices;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'switch_theme'                              => 'truncate_tables',
			'permalink_structure_changed'               => 'truncate_tables',
			'rocket_domain_options_changed'             => 'truncate_tables',
			'wp_trash_post'                             => 'delete_post',
			'delete_post'                               => 'delete_post',
			'clean_post_cache'                          => 'delete_post',
			'wp_update_comment_count'                   => 'delete_post',
			'edit_term'                                 => 'delete_term',
			'pre_delete_term'                           => 'delete_term',
			'rocket_performance_hints_clean_all'        => 'truncate_from_admin',
			'rocket_performance_hints_clean_url'        => 'clean_url',
			'wp_rocket_upgrade'                         => [ 'truncate_on_update', 10, 2 ],
			'rocket_admin_bar_items'                    => [
				[ 'add_clear_performance_hints_menu_item' ],
				[ 'add_clear_url_performance_hints_menu_item' ],
			],
			'admin_notices'                             => [
				[ 'clean_performance_hint_result' ],
			],
			'rocket_dashboard_actions'                  => 'display_dashboard_button',
			'admin_post_rocket_clean_performance_hints' => 'clean_performance_hints',
			'admin_post_rocket_clean_performance_hints_url' => 'clean_url_performance_hints',
		];
	}

	/**
	 * Callback for truncating performance hints tables
	 *
	 * @return void
	 */
	public function truncate_tables(): void {
		$this->controller->truncate_tables();
	}

	/**
	 * Callback for deleting row or update post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return void
	 */
	public function delete_post( int $post_id ): void {
		$this->controller->delete_post( $post_id );
	}

	/**
	 * Callback for Deleting Performance hints optimization row on update or delete term.
	 *
	 * @param int $term_id The term ID.
	 *
	 * @return void
	 */
	public function delete_term( int $term_id ): void {
		$this->controller->delete_term( $term_id );
	}

	/**
	 * Deletes rows when triggering clean from admin
	 *
	 * @param array $clean An array containing the status and message.
	 *
	 * @return array
	 */
	public function truncate_from_admin( $clean ): array {
		return $this->controller->truncate_from_admin( $clean );
	}

	/**
	 * Cleans rows for the current URL.
	 *
	 * @return void
	 */
	public function clean_url() {
		$this->controller->clean_url();
	}

	/**
	 * Truncate Performance hints optimization tables on update to 3.16.1 and higher
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Old plugin version.
	 *
	 * @return void
	 */
	public function truncate_on_update( $new_version, $old_version ) {
		$this->controller->truncate_on_update( $new_version, $old_version );
	}

	/**
	 * Add clear performance hints data to WP Rocket admin bar menu
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @return void
	 */
	public function add_clear_performance_hints_menu_item( WP_Admin_Bar $wp_admin_bar ): void {
		$this->admin_bar->add_clear_performance_menu_item( $wp_admin_bar );
	}

	/**
	 * Add clear performance data hints for current url to WP Rocket admin bar menu
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @return void
	 */
	public function add_clear_url_performance_hints_menu_item( WP_Admin_Bar $wp_admin_bar ): void {
		$this->admin_bar->add_clear_url_performance_hints_menu_item( $wp_admin_bar );
	}

	/**
	 * Display the dashboard button to clear performance data hints features
	 *
	 * @return void
	 */
	public function display_dashboard_button() {
		$this->admin_bar->display_dashboard_button();
	}

	/**
	 * Truncate Performance Hints tables when clicking on the dashboard button/menu
	 *
	 * @return void
	 */
	public function clean_performance_hints(): void {
		$this->clean->clean_performance_hints();
	}

	/**
	 * Truncate performance hints the current URL.
	 *
	 * @return void
	 */
	public function clean_url_performance_hints(): void {
		$this->clean->clean_url_performance_hints();
	}

	/**
	 * Show admin notice after clearing Performance Hints tables.
	 *
	 * @return void
	 */
	public function clean_performance_hint_result(): void {
		$this->notices->clean_performance_hint_result();
	}
}
