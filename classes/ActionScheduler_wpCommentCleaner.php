<?php

/**
 * Class ActionScheduler_WPCommentCleaner
 *
 * @since 3.0.0
 */
class ActionScheduler_WPCommentCleaner {

	/**
	 * Post migration hook used to cleanup the WP comment table.
	 *
	 * @var string
	 */
	protected static $cleanup_hook = 'action_scheduler/cleanup_wp_comment_logs';

	/**
	 * An instance of the ActionScheduler_wpCommentLogger class to interact with the comments table.
	 *
	 * This instance should only be used as an interface. It should not be initialized.
	 *
	 * @var ActionScheduler_wpCommentLogger
	 */
	protected static $wp_comment_logger = null;

	/**
	 * Initialize the class and attach callbacks.
	 */
	public static function init() {
		if ( empty( self::$wp_comment_logger ) ) {
			self::$wp_comment_logger = new ActionScheduler_wpCommentLogger();
		}

		add_action( self::$cleanup_hook, array( __CLASS__, 'delete_all_action_comments' ) );

		// While there are orphaned logs left in the comments table, we need to attach the callbacks which filter comment counts.
		add_action( 'pre_get_comments', array( self::$wp_comment_logger, 'filter_comment_queries' ), 10, 1 );
		add_action( 'wp_count_comments', array( self::$wp_comment_logger, 'filter_comment_count' ), 20, 2 ); // run after WC_Comments::wp_count_comments() to make sure we exclude order notes and action logs
		add_action( 'comment_feed_where', array( self::$wp_comment_logger, 'filter_comment_feed' ), 10, 2 );

		// Action Scheduler may be displayed as a Tools screen or WooCommerce > Status administration screen
		add_action( 'load-tools_page_action-scheduler', array( __CLASS__, 'print_admin_notice' ) );
		add_action( 'load-woocommerce_page_wc-status', array( __CLASS__, 'print_admin_notice' ) );
	}

	/**
	 * Determines if there are log entries in the wp comments table.
	 *
	 * @return boolean Whether there are scheduled action comments in the comments table.
	 */
	public static function has_logs() {
		return (bool) get_comments( array( 'type' => ActionScheduler_wpCommentLogger::TYPE, 'number' => 1, 'fields' => 'ids' ) );
	}

	/**
	 * Schedules the WP Post comment table cleanup to run in 6 months if it's not already scheduled.
	 * Attached to the migration complete hook 'action_scheduler/migration_complete'.
	 */
	public static function maybe_schedule_cleanup() {
		if ( ! as_next_scheduled_action( self::$cleanup_hook ) && self::has_logs() ) {
			as_schedule_single_action( gmdate( U ) + ( 6 * MONTH_IN_SECONDS ), self::$cleanup_hook );
		}
	}

	/**
	 * Delete all action comments from the WP Comments table.
	 */
	public static function delete_all_action_comments() {
		global $wpdb;
		$wpdb->delete( $wpdb->comments, array( 'comment_type' => ActionScheduler_wpCommentLogger::TYPE, 'comment_agent' => ActionScheduler_wpCommentLogger::AGENT ) );
	}

	/**
	 * Prints details about the orphaned action logs and includes information on where to learn more.
	 */
	public static function print_admin_notice() {
		$notice = sprintf( __( 'Action Scheduler has completed the data migration, however, there remains scheduled action logs left in the WordPress Comments table. After 6 months, they will automatically be deleted. Click here to %slearn more%s.' ), '<a href="https://github.com/Prospress/action-scheduler">' , '</a>' );
		echo '<div class="notice notice-warning"><p>' . wp_kses_post( $notice ) . '</p></div>';
	}
}
