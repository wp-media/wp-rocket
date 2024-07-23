<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * AdminContext instance.
	 *
	 * @var AdminContext
	 */
	private $admin_context;

	/**
	 * Instantiate the class
	 *
	 * @param AdminContext $admin_context Admin context instance.
	 */
	public function __construct( AdminContext $admin_context ) {
		$this->admin_context = $admin_context;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'switch_theme'                  => 'truncate_performance_table',
			'permalink_structure_changed'   => 'truncate_performance_table',
			'rocket_domain_options_changed' => 'truncate_performance_table',
			'wp_trash_post'                 => 'delete_post',
			'delete_post'                   => 'delete_post',
			'clean_post_cache'              => 'delete_post',
			'wp_update_comment_count'       => 'delete_post',
			'edit_term'                     => 'delete_term',
			'pre_delete_term'               => 'delete_term',
			'rocket_saas_clean_all'         => 'truncate_admin_rows',
		];
	}

	/**
	 * Callback for truncating performance table
	 *
	 * @return void
	 */
	public function truncate_performance_table(): void {
		$this->admin_context->truncate_performance_table();
	}

	/**
	 * Callback for deleting row or update post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return void
	 */
	public function delete_post( int $post_id ): void {
		$this->admin_context->delete_post( $post_id );
	}

	/**
	 * Callback for Delete ATF row on update or delete term.
	 *
	 * @param int $term_id The term ID.
	 *
	 * @return void
	 */
	public function delete_term( int $term_id ): void {
		$this->admin_context->delete_term( $term_id );
	}

	/**
	 * Deletes rows when triggering clean from admin
	 *
	 * @param array $clean An array containing the status and message.
	 *
	 * @return array
	 */
	public function truncate_admin_rows( array $clean ): array {
		return $this->admin_context->truncate_admin_rows( $clean );
	}
}
