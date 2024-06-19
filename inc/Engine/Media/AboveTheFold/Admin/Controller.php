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

		$this->table->truncate_atf_table();

		/**
		 * Fires after clearing lcp & atf data.
		 */
		do_action( 'rocket_after_clear_atf' );
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

	/**
	 * Deletes the ATF when updating a term
	 *
	 * @param int $term_id the term ID.
	 *
	 * @return void
	 */
	public function delete_term_atf( $term_id ) {
		if ( ! $this->context->is_allowed() ) {
			return;
		}

		$url = get_term_link( (int) $term_id );

		if ( is_wp_error( $url ) ) {
			return;
		}

		$this->query->delete_by_url( untrailingslashit( $url ) );
	}

	/**
	 * Deletes rows when triggering clean from admin
	 *
	 * @param array $clean An array containing the status and message.
	 *
	 * @return array
	 */
	public function truncate_atf_admin( $clean ) {
		if ( ! $this->context->is_allowed() ) {
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
	 * Cleans rows for the current URL.
	 *
	 * @return void
	 */
	public function clean_url() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			wp_nonce_ays( '' );
		}

		$url = wp_get_referer();

		if ( 0 !== strpos( $url, 'http' ) ) {
			$parse_url = get_rocket_parse_url( untrailingslashit( home_url() ) );
			$url       = $parse_url['scheme'] . '://' . $parse_url['host'] . $url;
		}

		$this->query->delete_by_url( untrailingslashit( $url ) );
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
		if ( version_compare( $old_version, '3.16.1', '>=' ) ) {
			return;
		}

		$this->truncate_atf();
	}
}
