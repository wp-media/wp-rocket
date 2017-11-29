<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Extends the background process class for the database optimization background process.
 *
 * @since 2.11
 * @author Remy Perona
 *
 * @see WP_Background_Process
 */
class Rocket_Background_Database_Optimization extends WP_Background_Process {
	/**
	 * Prefix
	 *
	 * @var string
	 * @access protected
	 */
	protected $prefix = 'rocket';

	/**
	 * Specific action identifier for sitemap preload.
	 *
	 * @access protected
	 * @var string Action identifier
	 */
	protected $action = 'database_optimization';

	/**
	 * Count the number of optimized items.
	 *
	 * @access protected
	 * @var array $count An array of indexed number of optimized items.
	 */
	protected $count = array();

	/**
	 * Dispatch
	 *
	 * @access public
	 * @return array|WP_Error
	 */
	public function dispatch() {
		set_transient( 'rocket_database_optimization_process', 'running', HOUR_IN_SECONDS );

		// Perform remote post.
		return parent::dispatch();
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
			case 'revisions':
				$query = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type = 'revision'" );
				if ( $query ) {
					$number = 0;
					foreach ( $query as $id ) {
						$number += (int) wp_delete_post_revision( intval( $id ) );
					}

					$this->count[ $item ] = $number;
				}
				break;
			case 'auto_drafts':
				$query = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_status = 'auto-draft'" );
				if ( $query ) {
					$number = 0;
					foreach ( $query as $id ) {
						$number += (int) wp_delete_post( intval( $id ), true );
					}

					$this->count[ $item ] = $number;
				}
				break;
			case 'trashed_posts':
				$query = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_status = 'trash'" );
				if ( $query ) {
					$number = 0;
					foreach ( $query as $id ) {
						$number += (int) wp_delete_post( $id, true );
					}

					$this->count[ $item ] = $number;
				}
				break;
			case 'spam_comments':
				$query = $wpdb->get_col( "SELECT comment_ID FROM $wpdb->comments WHERE comment_approved = 'spam'" );
				if ( $query ) {
					$number = 0;
					foreach ( $query as $id ) {
						$number += (int) wp_delete_comment( intval( $id ), true );
					}

					$this->count[ $item ] = $number;
				}
				break;
			case 'trashed_comments':
				$query = $wpdb->get_col( "SELECT comment_ID FROM $wpdb->comments WHERE (comment_approved = 'trash' OR comment_approved = 'post-trashed')" );
				if ( $query ) {
					$number = 0;
					foreach ( $query as $id ) {
						$number += (int) wp_delete_comment( intval( $id ), true );
					}

					$this->count[ $item ] = $number;
				}
				break;
			case 'expired_transients':
				$time = isset( $_SERVER['REQUEST_TIME'] ) ? (int) $_SERVER['REQUEST_TIME'] : time();
				$query = $wpdb->get_col( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '_transient_timeout%' AND option_value < $time" );

				if ( $query ) {
					$number = 0;
					foreach ( $query as $transient ) {
						$key = str_replace( '_transient_timeout_', '', $transient );
						$number += (int) delete_transient( $key );
					}

					$this->count[ $item ] = $number;
				}
				break;
			case 'all_transients':
				$query = $wpdb->get_col( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'" );
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
			case 'optimize_tables':
				$query = $wpdb->get_results( "SELECT table_name, data_free FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "' and Engine <> 'InnoDB' and data_free > 0" );
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
