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
			'wp_trash_post'                 => 'delete_atf',
			'delete_post'                   => 'delete_atf',
			'clean_post_cache'              => 'delete_atf',
			'wp_update_comment_count'       => 'delete_atf',
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
}
