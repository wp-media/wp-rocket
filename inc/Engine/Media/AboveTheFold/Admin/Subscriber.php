<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Admin controller instance
	 *
	 * @var Controller
	 */
	private $controller;

	/**
	 * Constructor
	 *
	 * @param Controller $controller ATF Admin controller instance.
	 */
	public function __construct( Controller $controller ) {
		$this->controller = $controller;
	}

	/**
	 * Array of events this subscriber listens to
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'switch_theme'                  => 'truncate_atf',
			'permalink_structure_changed'   => 'truncate_atf',
			'rocket_domain_options_changed' => 'truncate_atf',
			'wp_trash_post'                 => 'delete_post_atf',
			'delete_post'                   => 'delete_post_atf',
			'clean_post_cache'              => 'delete_post_atf',
			'wp_update_comment_count'       => 'delete_post_atf',
			'edit_term'                     => 'delete_term_atf',
			'pre_delete_term'               => 'delete_term_atf',
			'rocket_saas_clean_all'         => 'truncate_atf_admin',
			'rocket_saas_clean_url'         => 'clean_url',
			'wp_rocket_upgrade'             => [ 'truncate_on_update', 10, 2 ],
		];
	}

	/**
	 * Truncate delete ATF DB table.
	 *
	 * @return void
	 */
	public function truncate_atf() {
		$this->controller->truncate_atf();
	}

	/**
	 * Delete ATF row on update Post or delete post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return void
	 */
	public function delete_post_atf( $post_id ) {
		$this->controller->delete_post_atf( $post_id );
	}

	/**
	 * Delete ATF row on update or delete term.
	 *
	 * @param int $term_id the term ID.
	 *
	 * @return void
	 */
	public function delete_term_atf( $term_id ) {
		$this->controller->delete_term_atf( $term_id );
	}

	/**
	 * Deletes rows when triggering clean from admin
	 *
	 * @param array $clean An array containing the status and message.
	 *
	 * @return array
	 */
	public function truncate_atf_admin( $clean ) {
		return $this->controller->truncate_atf_admin( $clean );
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
	 * Truncate ATF table on update to 3.16.1 and higher
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Old plugin version.
	 *
	 * @return void
	 */
	public function truncate_on_update( $new_version, $old_version ) {
		$this->controller->truncate_on_update( $new_version, $old_version );
	}
}
