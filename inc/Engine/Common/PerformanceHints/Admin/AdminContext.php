<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Admin;

use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Tables\AboveTheFold as ATFTable;

class AdminContext {
	/**
	 * Array of Factories.
	 *
	 * @var array
	 */
	private $factories;

	/**
	 * ATF Table instance
	 *
	 * @var ATFTable
	 */
	private $table;

	/**
	 * Context instance
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * ATF Query instance
	 *
	 * @var ATFQuery
	 */
	private $query;

	/**
	 * Instantiate the class.
	 *
	 * @param array    $factories Array of factories.
	 * @param ATFTable $table ATF Table instance.
	 * @param ATFQuery $query Queries instance.
	 * @param Context  $context Context instance.
	 */
	public function __construct( array $factories, ATFTable $table, ATFQuery $query, Context $context ) {
		$this->factories = $factories;
		$this->table     = $table;
		$this->query     = $query;
		$this->context   = $context;
	}

	/**
	 * Deletes rows when triggering clean from admin
	 *
	 * @param array $clean An array containing the status and message.
	 *
	 * @return array
	 */
	public function truncate_admin_rows( array $clean ): array {
		if ( ! $this->context->is_allowed() || empty( $this->factories ) ) {
			return $clean;
		}

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return [
				'status' => 'die',
			];
		}

		$this->delete_rows();

		return [
			'status'  => 'success',
			'message' => sprintf(
			// translators: %1$s = plugin name.
				__( '%1$s: Critical images cleared!', 'rocket' ),
				'<strong>WP Rocket</strong>'
			),
		];
	}

	/**
	 * Deletes the rows from the table
	 *
	 * @return void
	 */
	private function delete_rows() {
		if ( 0 < $this->query->get_not_completed_count() ) {
			$this->table->remove_all_completed_rows();
			return;
		}

		$this->table->truncate_atf_table();

		/**
		 * Fires after clearing lcp & atf data.
		 */
		do_action( 'rocket_after_clear_atf' );
	}

	/**
	 * Truncate delete ATF DB table.
	 *
	 * @return void
	 */
	public function truncate_performance_table(): void {
		if ( ! $this->context->is_allowed() || empty( $this->factories ) ) {
			return;
		}

		$this->delete_rows();
	}

	/**
	 * Delete performance hints row on update Post or delete post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return void
	 */
	public function delete_post( int $post_id ): void {
		if ( ! $this->context->is_allowed() || empty( $this->factories ) ) {
			return;
		}

		$url = get_permalink( $post_id );

		if ( false === $url ) {
			return;
		}

		$this->query->delete_by_url( untrailingslashit( $url ) );
	}

	/**
	 * Deletes the ATF when updating a term
	 *
	 * @param int $term_id the term ID.
	 *
	 * @return void
	 */
	public function delete_term( int $term_id ): void {
		if ( ! $this->context->is_allowed() || empty( $this->factories ) ) {
			return;
		}

		$url = get_term_link( (int) $term_id );

		if ( is_wp_error( $url ) ) {
			return;
		}

		$this->query->delete_by_url( untrailingslashit( $url ) );
	}
}
