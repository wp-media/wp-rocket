<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Admin;

class Controller {

	/**
	 * Array of factories
	 *
	 * @var array
	 */
	private $factories;

	/**
	 * Constructor
	 *
	 * @param array $factories Array of factories.
	 */
	public function __construct( array $factories ) {
		$this->factories = $factories;
	}

	/**
	 * Truncate performance hints optimization tables.
	 *
	 * @return void
	 */
	public function truncate_tables() {
		$this->delete_rows();
	}

	/**
	 * Deletes the rows from the table
	 *
	 * @return void
	 */
	private function delete_rows() {
		foreach ( $this->factories as $factory ) {
			if ( 0 < $factory->queries()->get_not_completed_count() ) {
				$factory->table()->remove_all_completed_rows();
				continue;
			}

			$factory->table()->truncate_table();
		}

		/**
		 * Fires after clearing performance hints optimization data.
		 */
		rocket_do_action_and_deprecated(
			'rocket_after_clear_performance_hints_data',
			[],
			'3.16.4',
			'rocket_after_clear_atf'
		);
	}

	/**
	 * Delete row on update Post or delete post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return void
	 */
	public function delete_post( $post_id ) {
		$url = get_permalink( $post_id );

		// get_permalink should return false or string, but some plugins return null.
		if ( ! is_string( $url ) ) {
			return;
		}

		$this->delete_by_url( $url );
	}

	/**
	 * Deletes performance hints optimizations when updating a term
	 *
	 * @param int $term_id the term ID.
	 *
	 * @return void
	 */
	public function delete_term( $term_id ) {
		$url = get_term_link( (int) $term_id );

		if ( is_wp_error( $url ) ) {
			return;
		}

		$this->delete_by_url( $url );
	}

	/**
	 * Should allow early if true.
	 *
	 * @return bool
	 */
	private function is_allowed(): bool {
		$allowed = false;

		foreach ( $this->factories as $factory ) {
			if ( $factory->get_context()->is_allowed() ) {
				$allowed = true;
				break;
			}
		}

		return $allowed;
	}

	/**
	 * Deletes rows when triggering clean from admin
	 *
	 * @param array $clean An array containing the status and message.
	 *
	 * @return array
	 */
	public function truncate_from_admin( $clean ) {
		if ( ! $this->is_allowed() ) {
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
				__( '%1$s: Critical images and Lazy Render data was cleared!', 'rocket' ),
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

		/**
		 * Fires after clearing performance hints data for specific url.
		 *
		 * @param string $url Current page URL.
		 */
		do_action( 'rocket_performance_hints_data_after_clearing', $url );

		$this->delete_by_url( $url );
	}

	/**
	 * Truncate Performance hints optimization tables on update to 3.16.1.1 and higher
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

		if ( ! $this->is_allowed() ) {
			return;
		}

		$this->truncate_tables();
	}

	/**
	 * Deletes row by url from table.
	 *
	 * @param string $url Url to delete.
	 * @return void
	 */
	private function delete_by_url( string $url ) {
		foreach ( $this->factories as $factory ) {
			$factory->queries()->delete_by_url( untrailingslashit( $url ) );
		}
	}
}
