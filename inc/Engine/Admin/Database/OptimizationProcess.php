<?php
namespace WP_Rocket\Engine\Admin\Database;

use WP_Rocket_WP_Background_Process;

/**
 * Extends the background process class for the database optimization background process.
 *
 * @see WP_Rocket_WP_Background_Process
 */
class OptimizationProcess extends WP_Rocket_WP_Background_Process {
	/**
	 * Prefix
	 *
	 * @var string
	 */
	protected $prefix = 'rocket';

	/**
	 * Specific action identifier for sitemap preload.
	 *
	 * @var string Action identifier
	 */
	protected $action = 'database_optimization';

	/**
	 * Count the number of optimized items.
	 *
	 * @var array $count An array of indexed number of optimized items.
	 */
	protected $count = [];

	/**
	 * Dispatch
	 *
	 * @return void
	 */
	public function dispatch() {
		set_transient( 'rocket_database_optimization_process', 'running', HOUR_IN_SECONDS );

		// Perform remote post.
		parent::dispatch();
	}

	/**
	 * Perform the optimization corresponding to $item
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return bool false
	 */
	protected function task( $item ) {
		global $wpdb;

		switch ( $item ) {
			case 'database_revisions':
				$query = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type = 'revision'" );
				if ( $query ) {
					$number = 0;
					foreach ( $query as $id ) {
						$number += wp_delete_post_revision( intval( $id ) ) instanceof \WP_Post ? 1 : 0;
					}

					$this->count[ $item ] = $number;
				}
				break;
			case 'database_auto_drafts':
				$query = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_status = 'auto-draft'" );
				if ( $query ) {
					$number = 0;
					foreach ( $query as $id ) {
						$number += wp_delete_post( intval( $id ), true ) instanceof \WP_Post ? 1 : 0;
					}

					$this->count[ $item ] = $number;
				}
				break;
			case 'database_trashed_posts':
				$query = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_status = 'trash'" );
				if ( $query ) {
					$number = 0;
					foreach ( $query as $id ) {
						$number += wp_delete_post( $id, true ) instanceof \WP_Post ? 1 : 0;
					}

					$this->count[ $item ] = $number;
				}
				break;
			case 'database_spam_comments':
				$query = $wpdb->get_col( "SELECT comment_ID FROM $wpdb->comments WHERE comment_approved = 'spam'" );
				if ( $query ) {
					$number = 0;
					foreach ( $query as $id ) {
						$number += (int) wp_delete_comment( intval( $id ), true );
					}

					$this->count[ $item ] = $number;
				}
				break;
			case 'database_trashed_comments':
				$query = $wpdb->get_col( "SELECT comment_ID FROM $wpdb->comments WHERE (comment_approved = 'trash' OR comment_approved = 'post-trashed')" );
				if ( $query ) {
					$number = 0;
					foreach ( $query as $id ) {
						$number += (int) wp_delete_comment( intval( $id ), true );
					}

					$this->count[ $item ] = $number;
				}
				break;
			case 'database_all_transients':
				$query = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s", $wpdb->esc_like( '_transient_' ) . '%', $wpdb->esc_like( '_site_transient_' ) . '%' ) );
				if ( $query ) {
					$number = 0;
					foreach ( $query as $transient ) {
						if ( strpos( $transient, '_site_transient_' ) !== false ) {
							$number += (int) delete_site_transient( str_replace( '_site_transient_', '', $transient ) );
						} else {
							$number += (int) delete_transient( str_replace( '_transient_', '', $transient ) );
						}
					}

					$this->count[ $item ] = $number;
				}
				break;
			case 'database_optimize_tables':
				$query = $wpdb->get_results( "SELECT table_name AS table_name, data_free AS data_free FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "' and Engine <> 'InnoDB' and data_free > 0" );
				if ( $query ) {
					$number = 0;
					foreach ( $query as $table ) {
						$number += (int) $wpdb->query( "OPTIMIZE TABLE $table->table_name" );
					}

					$this->count[ $item ] = $number;
				}
				break;
		}

		return false;
	}

	/**
	 * Complete
	 */
	protected function complete() {
		delete_transient( 'rocket_database_optimization_process' );
		set_transient( 'rocket_database_optimization_process_complete', $this->count );

		parent::complete();
	}
}
