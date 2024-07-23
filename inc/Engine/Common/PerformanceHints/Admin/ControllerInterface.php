<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Admin;

interface ControllerInterface {
	/**
	 * Truncate performance table
	 *
	 * @return void
	 */
	public function truncate_performance_table(): void;

	/**
	 * Delete post
	 *
	 * @param int $post_id Post id that will be deleted.
	 *
	 * @return void
	 */
	public function delete_post( int $post_id ): void;

	/**
	 * Delete term
	 *
	 * @param int $term_id Term ID to be deleted.
	 *
	 * @return void
	 */
	public function delete_term( int $term_id ): void;

	/**
	 * Deletes rows when triggering clean from admin
	 *
	 * @param array $clean An array containing the status and message.
	 *
	 * @return array
	 */
	public function truncate_admin_rows( array $clean ): array;
}
