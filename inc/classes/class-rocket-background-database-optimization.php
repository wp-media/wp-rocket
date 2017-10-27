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
				$query = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", 'revision' ) );
				if ( $query ) {
					foreach ( $query as $id ) {
						wp_delete_post_revision( intval( $id ) );
					}

					$number = count( $query );
					$this->count[ $item ] = $number;
				}
				break;
			case 'auto_drafts':
				$query = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_status = %s", 'auto-draft' ) );
				if ( $query ) {
					foreach ( $query as $id ) {
						wp_delete_post( intval( $id ), true );
					}

					$number = count( $query );
					$this->count[ $item ] = $number;
				}
				break;
			case 'trashed_posts':
				$query = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_status = %s", 'trash' ) );
				if ( $query ) {
					foreach ( $query as $id ) {
						wp_delete_post( $id, true );
					}

					$number = count( $query );
					$this->count[ $item ] = $number;
				}
				break;
			case 'spam_comments':
				$query = $wpdb->get_col( $wpdb->prepare( "SELECT comment_ID FROM $wpdb->comments WHERE comment_approved = %s", 'spam' ) );
				if ( $query ) {
					foreach ( $query as $id ) {
						wp_delete_comment( intval( $id ), true );
					}

					$number = count( $query );
					$this->count[ $item ] = $number;
				}
				break;
			case 'trashed_comments':
				$query = $wpdb->get_col( $wpdb->prepare( "SELECT comment_ID FROM $wpdb->comments WHERE (comment_approved = %s OR comment_approved = %s)", 'trash', 'post-trashed' ) );
				if ( $query ) {
					foreach ( $query as $id ) {
						wp_delete_comment( intval( $id ), true );
					}

					$number = count( $query );
					$this->count[ $item ] = $number;
				}
				break;
			case 'expired_transients':
				$time = isset( $_SERVER['REQUEST_TIME'] ) ? (int) $_SERVER['REQUEST_TIME'] : time();
				$query = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s AND option_value < %s", '_transient_timeout%', $time ) );

				if ( $query ) {
					foreach ( $query as $transient ) {
						$key = str_replace( '_transient_timeout_', '', $transient );
						delete_transient( $key );
					}

					$number = count( $query );
					$this->count[ $item ] = $number;
				}
				break;
			case 'all_transients':
				$query = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s", '%_transient_%' ) );
				if ( $query ) {
					foreach ( $query as $transient ) {
						if ( strpos( $transient, '_site_transient_' ) !== false ) {
							delete_site_transient( str_replace( '_site_transient_', '', $transient ) );
						} else {
							delete_transient( str_replace( '_transient_', '', $transient ) );
						}
					}

					$number = count( $query );
					$this->count[ $item ] = $number;
				}
				break;
			case 'optimize_tables':
				$query = $wpdb->get_results( $wpdb->prepare( "SELECT table_name, data_free FROM information_schema.tables WHERE table_schema = %s and Engine <> 'InnoDB' and data_free > 0", DB_NAME ) );
				if ( $query ) {
					foreach ( $query as $table ) {
						$wpdb->query( $wpdb->prepare( 'OPTIMIZE TABLE %s', $table->table_name ) );
					}

					$number = count( $query );
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
