<?php
namespace WP_Rocket\Admin\Database;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Handles the database optimization process.
 *
 * @since 2.11
 * @author Remy Perona
 */
class Optimization {
	/**
	 * Background process instance
	 *
	 * @since 2.11
	 * @var Optimization_Process $process Background Process instance.
	 * @access protected
	 */
	protected $process;

	/**
	 * Array of option name/label pairs.
	 *
	 * @var array
	 * @access private
	 */
	private $options;

	/**
	 * Class constructor.
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param Optimization_Process $process Background process instance.
	 */
	public function __construct( Optimization_Process $process ) {
		$this->process = $process;
		$this->options = [
			'database_revisions'          => __( 'Revisions', 'rocket' ),
			'database_auto_drafts'        => __( 'Auto Drafts', 'rocket' ),
			'database_trashed_posts'      => __( 'Trashed Posts', 'rocket' ),
			'database_spam_comments'      => __( 'Spam Comments', 'rocket' ),
			'database_trashed_comments'   => __( 'Trashed Comments', 'rocket' ),
			'database_expired_transients' => __( 'Expired transients', 'rocket' ),
			'database_all_transients'     => __( 'Transients', 'rocket' ),
			'database_optimize_tables'    => __( 'Tables', 'rocket' ),
		];
	}

	/**
	 * Get Database options
	 *
	 * @since 3.0.4
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Performs the database optimization
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param array $options WP Rocket Database options.
	 */
	public function process_handler( $options ) {
		if ( method_exists( $this->process, 'cancel_process' ) ) {
			$this->process->cancel_process();
		}

		array_map( array( $this->process, 'push_to_queue' ), $options );

		$this->process->save()->dispatch();
	}

	/**
	 * Count the number of items concerned by the database cleanup
	 *
	 * @since 2.8
	 * @author Remy Perona
	 *
	 * @param string $type Item type to count.
	 * @return int Number of items for this type
	 */
	public function count_cleanup_items( $type ) {
		global $wpdb;

		$count = 0;

		switch ( $type ) {
			case 'database_revisions':
				$count = $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = 'revision'" );
				break;
			case 'database_auto_drafts':
				$count = $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_status = 'auto-draft'" );
				break;
			case 'database_trashed_posts':
				$count = $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_status = 'trash'" );
				break;
			case 'database_spam_comments':
				$count = $wpdb->get_var( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_approved = 'spam'" );
				break;
			case 'database_trashed_comments':
				$count = $wpdb->get_var( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE (comment_approved = 'trash' OR comment_approved = 'post-trashed')" );
				break;
			case 'database_expired_transients':
				$time  = isset( $_SERVER['REQUEST_TIME'] ) ? (int) $_SERVER['REQUEST_TIME'] : time();
				$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(option_name) FROM $wpdb->options WHERE option_name LIKE %s AND option_value < %d", $wpdb->esc_like( '_transient_timeout' ) . '%', $time ) );
				break;
			case 'database_all_transients':
				$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(option_id) FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s", $wpdb->esc_like( '_transient_' ) . '%', $wpdb->esc_like( '_site_transient_' ) . '%' ) );
				break;
			case 'database_optimize_tables':
				$count = $wpdb->get_var( "SELECT COUNT(table_name) FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "' and Engine <> 'InnoDB' and data_free > 0" );
				break;
		}

		return $count;
	}
}
