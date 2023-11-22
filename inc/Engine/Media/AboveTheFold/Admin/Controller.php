<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Admin;

use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Tables\AboveTheFold as ATFTable;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;

class Controller {
	/**
	 * ATF Table instance
	 *
	 * @var ATFTable
	 */
	private $table;

	/**
	 * ATF Query instance
	 *
	 * @var ATFQuery
	 */
	private $query;

	/**
	 * Context instance
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Constructor
	 *
	 * @param ATFTable $table Table instance.
	 * @param ATFQuery $query ATF Query instance.
	 * @param Context  $context Context instance.
	 */
	public function __construct( ATFTable $table, ATFQuery $query, Context $context ) {
		$this->table   = $table;
		$this->query   = $query;
		$this->context = $context;
	}

	/**
	 * Truncate delete ATF DB table.
	 *
	 * @return void
	 */
	public function truncate_atf() {
		if ( ! $this->context->is_allowed() ) {
			return;
		}

		$this->delete_rows();
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

		$this->table->truncate();
	}

	/**
	 * Delete ATF row on update Post or delete post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return void
	 */
	public function delete_post_atf( $post_id ) {
		if ( ! $this->context->is_allowed() ) {
			return;
		}

		$url = get_permalink( $post_id );

		if ( false === $url ) {
			return;
		}

		$this->query->delete_by_url( untrailingslashit( $url ) );
	}
}
