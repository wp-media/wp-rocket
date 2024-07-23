<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Array of Factories.
	 *
	 * @var array
	 */
	private $factories;

	/**
	 * Instantiate the class
	 *
	 * @param array $factories Array of factories.
	 */
	public function __construct( array $factories ) {
		$this->factories = $factories;
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
		foreach ( $this->factories as $factory ) {
			$factory->get_admin_controller()->truncate_performance_table();
		}
	}

	/**
	 * Callback for deleting row or update post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return void
	 */
	public function delete_post( int $post_id ): void {
		foreach ( $this->factories as $factory ) {
			$factory->get_admin_controller()->delete_post( $post_id );
		}
	}

	/**
	 * Callback for Delete ATF row on update or delete term.
	 *
	 * @param int $term_id The term ID.
	 *
	 * @return void
	 */
	public function delete_term( int $term_id ): void {
		foreach ( $this->factories as $factory ) {
			$factory->get_admin_controller()->delete_term( $term_id );
		}
	}

	/**
	 * Deletes rows when triggering clean from admin
	 *
	 * @param array $clean An array containing the status and message.
	 *
	 * @return array
	 */
	public function truncate_atf_admin( $clean ) {
		foreach ( $this->factories as $factory ) {
			$factory->get_admin_controller()->truncate_admin_rows( $clean );
		}
		return $this->controller->truncate_admin_rows( $clean );
	}
}
