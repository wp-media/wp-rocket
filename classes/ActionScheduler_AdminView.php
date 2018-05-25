<?php

/**
 * Class ActionScheduler_AdminView
 * @codeCoverageIgnore
 */
class ActionScheduler_AdminView {

	private static $admin_view = NULL;

	/**
	 * @return ActionScheduler_QueueRunner
	 * @codeCoverageIgnore
	 */
	public static function instance() {

		if ( empty( self::$admin_view ) ) {
			$class = apply_filters('action_scheduler_admin_view_class', 'ActionScheduler_AdminView');
			self::$admin_view = new $class();
		}

		return self::$admin_view;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function init() {
		if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || false == DOING_AJAX ) ) {

			if ( class_exists( 'WooCommerce' ) ) {
				add_action( 'woocommerce_admin_status_content_action-scheduler', array( $this, 'render_admin_ui' ) );
				add_filter( 'woocommerce_admin_status_tabs', array( $this, 'register_system_status_tab' ) );
			}

			add_action( 'admin_menu', array( $this, 'register_menu' ) );
		}
	}


	/**
	 * Registers action-scheduler into WooCommerce > System status.
	 *
	 * @param array $tabs An associative array of tab key => label.
	 * @return array $tabs An associative array of tab key => label, including Action Scheduler's tabs
	 */
	public function register_system_status_tab( array $tabs ) {
		$tabs['action-scheduler'] = __( 'Scheduled Actions', 'action-scheduler' );

		return $tabs;
	}

	/**
	 * Include Action Scheduler's administration under the Tools menu.
	 *
	 * A menu under the Tools menu is important for backward compatibility (as that's
	 * where it started), and also provides more convenient access than the WooCommerce
	 * System Status page, and for sites where WooCommerce isn't active.
	 */
	public function register_menu() {
		add_submenu_page(
			'tools.php',
			__( 'Scheduled Actions', 'action-scheduler' ),
			__( 'Scheduled Actions', 'action-scheduler' ),
			'manage_options',
			'action-scheduler',
			array( $this, 'render_admin_ui' )
		);
	}

	/**
	 * Renders the Admin UI
	 */
	public function render_admin_ui() {
		$table = new ActionScheduler_ListTable( ActionScheduler::store(), ActionScheduler::logger(), ActionScheduler::runner() );
		$table->display_page();
	}

	/** Deprecated Functions **/

	public function action_scheduler_post_type_args( $args ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		return $args;
	}

	/**
	 * Customise the post status related views displayed on the Scheduled Actions administration screen.
	 *
	 * @param array $views An associative array of views and view labels which can be used to filter the 'scheduled-action' posts displayed on the Scheduled Actions administration screen.
	 * @return array $views An associative array of views and view labels which can be used to filter the 'scheduled-action' posts displayed on the Scheduled Actions administration screen.
	 */
	public function list_table_views( $views ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		return $views;
	}

	/**
	 * Do not include the "Edit" action for the Scheduled Actions administration screen.
	 *
	 * Hooked to the 'bulk_actions-edit-action-scheduler' filter.
	 *
	 * @param array $actions An associative array of actions which can be performed on the 'scheduled-action' post type.
	 * @return array $actions An associative array of actions which can be performed on the 'scheduled-action' post type.
	 */
	public function bulk_actions( $actions ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		return $actions;
	}

	/**
	 * Completely customer the columns displayed on the Scheduled Actions administration screen.
	 *
	 * Because we can't filter the content of the default title and date columns, we need to recreate our own
	 * custom columns for displaying those post fields. For the column content, @see self::list_table_column_content().
	 *
	 * @param array $columns An associative array of columns that are use for the table on the Scheduled Actions administration screen.
	 * @return array $columns An associative array of columns that are use for the table on the Scheduled Actions administration screen.
	 */
	public function list_table_columns( $columns ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		return $columns;
	}

	/**
	 * Make our custom title & date columns use defaulting title & date sorting.
	 *
	 * @param array $columns An associative array of columns that can be used to sort the table on the Scheduled Actions administration screen.
	 * @return array $columns An associative array of columns that can be used to sort the table on the Scheduled Actions administration screen.
	 */
	public static function list_table_sortable_columns( $columns ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		return $columns;
	}

	/**
	 * Print the content for our custom columns.
	 *
	 * @param string $column_name The key for the column for which we should output our content.
	 * @param int $post_id The ID of the 'scheduled-action' post for which this row relates.
	 */
	public static function list_table_column_content( $column_name, $post_id ) {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Hide the inline "Edit" action for all 'scheduled-action' posts.
	 *
	 * Hooked to the 'post_row_actions' filter.
	 *
	 * @param array $actions An associative array of actions which can be performed on the 'scheduled-action' post type.
	 * @return array $actions An associative array of actions which can be performed on the 'scheduled-action' post type.
	 */
	public static function row_actions( $actions, $post ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		return $actions;
	}

	/**
	 * Run an action when triggered from the Action Scheduler administration screen.
	 *
	 * @codeCoverageIgnore
	 */
	public static function maybe_execute_action() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Convert an interval of seconds into a two part human friendly string.
	 *
	 * The WordPress human_time_diff() function only calculates the time difference to one degree, meaning
	 * even if an action is 1 day and 11 hours away, it will display "1 day". This funciton goes one step
	 * further to display two degrees of accuracy.
	 *
	 * Based on Crontrol::interval() function by Edward Dale: https://wordpress.org/plugins/wp-crontrol/
	 *
	 * @param int $interval A interval in seconds.
	 * @return string A human friendly string representation of the interval.
	 */
	public static function admin_notices() {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Filter search queries to allow searching by Claim ID (i.e. post_password).
	 *
	 * @param string $orderby MySQL orderby string.
	 * @param WP_Query $query Instance of a WP_Query object
	 * @return string MySQL orderby string.
	 */
	public function custom_orderby( $orderby, $query ){
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Filter search queries to allow searching by Claim ID (i.e. post_password).
	 *
	 * @param string $search MySQL search string.
	 * @param WP_Query $query Instance of a WP_Query object
	 * @return string MySQL search string.
	 */
	public function search_post_password( $search, $query ) {
		_deprecated_function( __METHOD__, '2.0.0' );
	}

	/**
	 * Change messages when a scheduled action is updated.
	 *
	 * @param  array $messages
	 * @return array
	 */
	public function post_updated_messages( $messages ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		return $messages;
	}

}
