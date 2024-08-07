<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Controller instance.
	 *
	 * @var Controller
	 */
	private $controller;

	/**
	 * Instantiate the class
	 *
	 * @param Controller $controller Controller instance.
	 */
	public function __construct( Controller $controller ) {
		$this->controller = $controller;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'switch_theme'                  => 'truncate_tables',
			'permalink_structure_changed'   => 'truncate_tables',
			'rocket_domain_options_changed' => 'truncate_tables',
			'wp_trash_post'                 => 'delete_post',
			'delete_post'                   => 'delete_post',
			'clean_post_cache'              => 'delete_post',
			'wp_update_comment_count'       => 'delete_post',
			'edit_term'                     => 'delete_term',
			'pre_delete_term'               => 'delete_term',
			'rocket_saas_clean_all'         => 'truncate_from_admin',
			'rocket_saas_clean_url'         => 'clean_url',
			'wp_rocket_upgrade'             => [ 'truncate_on_update', 10, 2 ],
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
}
