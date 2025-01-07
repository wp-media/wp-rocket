<?php
namespace WP_Rocket\Engine\Admin\Database;

/**
 * Handles the database optimization process.
 */
class Optimization {
	/**
	 * Background process instance
	 *
	 * @var OptimizationProcess $process Background Process instance.
	 */
	protected $process;

	/**
	 * Class constructor.
	 *
	 * @param OptimizationProcess $process Background process instance.
	 */
	public function __construct( OptimizationProcess $process ) {
		$this->process = $process;
	}

	/**
	 * Get Database options
	 *
	 * @since 3.0.4
	 *
	 * @return array
	 */
	public function get_options() {
		return [
			'database_revisions'        => __( 'Revisions', 'rocket' ),
			'database_auto_drafts'      => __( 'Auto Drafts', 'rocket' ),
			'database_trashed_posts'    => __( 'Trashed Posts', 'rocket' ),
			'database_spam_comments'    => __( 'Spam Comments', 'rocket' ),
			'database_trashed_comments' => __( 'Trashed Comments', 'rocket' ),
			'database_all_transients'   => __( 'Transients', 'rocket' ),
			'database_optimize_tables'  => __( 'Tables', 'rocket' ),
		];
	}

	/**
	 * Performs the database optimization
	 *
	 * @since 2.11
	 *
	 * @param array $options WP Rocket Database options.
	 */
	public function process_handler( $options ) {
		if ( method_exists( $this->process, 'cancel_process' ) ) { // @phpstan-ignore-line
			$this->process->cancel_process();
		}

		array_map( [ $this->process, 'push_to_queue' ], $options );

		$this->process->save()->dispatch();
	}

	/**
	 * Count the number of items concerned by the database cleanup
	 *
	 * @since 2.8
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
