<?php

/**
 * Class ActionScheduler_AdminView
 * @codeCoverageIgnore
 */
class ActionScheduler_AdminView {

	private static $admin_view = NULL;

	private static $admin_url;

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

		if ( defined( 'WP_DEBUG' ) && true == WP_DEBUG && is_admin() && ( ! defined( 'DOING_AJAX' ) || false == DOING_AJAX ) ) {
			add_filter( 'action_scheduler_post_type_args', array( self::instance(), 'action_scheduler_post_type_args' ) );
		}

		self::$admin_url = admin_url( 'edit.php?post_type=' . ActionScheduler_wpPostStore::POST_TYPE );

		add_filter( 'views_edit-' . ActionScheduler_wpPostStore::POST_TYPE, array( self::instance(), 'list_table_views' ) );

		add_filter( 'bulk_actions-edit-' . ActionScheduler_wpPostStore::POST_TYPE, array( self::instance(), 'bulk_actions' ) );

		add_filter( 'manage_' . ActionScheduler_wpPostStore::POST_TYPE . '_posts_columns', array( self::instance(), 'list_table_columns' ), 1 );

		add_filter( 'manage_edit-' . ActionScheduler_wpPostStore::POST_TYPE . '_sortable_columns', array( self::instance(), 'list_table_sortable_columns' ) );

		add_filter( 'manage_' . ActionScheduler_wpPostStore::POST_TYPE . '_posts_custom_column', array( self::instance(), 'list_table_column_content' ), 10, 2 );

		add_filter( 'post_row_actions', array( self::instance(), 'row_actions' ), 10, 2 );

		add_action( 'admin_init', array( self::instance(), 'maybe_execute_action' ), 20 );

		add_action( 'admin_notices', array( self::instance(), 'admin_notices' ) );

		add_filter( 'post_updated_messages', array( self::instance(), 'post_updated_messages' ) );

		add_filter( 'posts_orderby', array( self::instance(), 'custom_orderby' ), 10, 2 );

		add_filter( 'posts_search', array( self::instance(), 'search_post_password' ), 10, 2 );
	}

	public function action_scheduler_post_type_args( $args ) {
		return array_merge( $args, array(
			'show_ui' => true,
			'show_in_menu' => 'tools.php',
			'show_in_admin_bar' => false,
		));
	}

	/**
	 * Customise the post status related views displayed on the Scheduled Actions administration screen.
	 *
	 * @param array $views An associative array of views and view labels which can be used to filter the 'scheduled-action' posts displayed on the Scheduled Actions administration screen.
	 * @return array $views An associative array of views and view labels which can be used to filter the 'scheduled-action' posts displayed on the Scheduled Actions administration screen.
	 */
	public function list_table_views( $views ) {

		foreach ( $views as $view_key => $view ) {
			if ( 'publish' == $view_key ) {
				$views[ $view_key ] = str_replace( __( 'Published', 'action-scheduler' ), __( 'Complete', 'action-scheduler' ), $view );
				break;
			}
		}

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

		if ( isset( $actions['edit'] ) ) {
			unset( $actions['edit'] );
		}

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

		$custom_columns = array(
			'cb'                    => $columns['cb'],
			'hook'                  => __( 'Hook', 'action-scheduler' ), // because we want to customise the inline actions
			'status'                => __( 'Status', 'action-scheduler' ),
			'args'                  => __( 'Arguments', 'action-scheduler' ),
			'taxonomy-action-group' => __( 'Group', 'action-scheduler' ),
			'recurrence'            => __( 'Recurrence', 'action-scheduler' ),
			'scheduled'             => __( 'Scheduled Date', 'action-scheduler' ), // because we want to customise how the date is displayed
		);

		if ( isset( $_REQUEST['post_status'] ) ) {
			if ( in_array( $_REQUEST['post_status'], array( 'failed', 'in-progress' ) ) ) {
				$custom_columns['modified'] = __( 'Started', 'action-scheduler' );
			} elseif ( 'publish' == $_REQUEST['post_status'] ) {
				$custom_columns['modified'] = __( 'Completed', 'action-scheduler' );
			}
		}

		$custom_columns['claim']    = __( 'Claim ID', 'action-scheduler' );
		$custom_columns['comments'] = __( 'Log', 'action-scheduler' );

		return $custom_columns;
	}

	/**
	 * Make our custom title & date columns use defaulting title & date sorting.
	 *
	 * @param array $columns An associative array of columns that can be used to sort the table on the Scheduled Actions administration screen.
	 * @return array $columns An associative array of columns that can be used to sort the table on the Scheduled Actions administration screen.
	 */
	public static function list_table_sortable_columns( $columns ) {

		$columns['hook']      = 'title';
		$columns['scheduled'] = array( 'date', true );
		$columns['modified']  = 'modified';
		$columns['claim']     = 'post_password';

		return $columns;
	}

	/**
	 * Print the content for our custom columns.
	 *
	 * @param string $column_name The key for the column for which we should output our content.
	 * @param int $post_id The ID of the 'scheduled-action' post for which this row relates.
	 * @return void
	 */
	public static function list_table_column_content( $column_name, $post_id ) {
		global $post;

		$action         = ActionScheduler::store()->fetch_action( $post_id );

		$action_title   = ( 'trash' == $post->post_status ) ? $post->post_title : $action->get_hook();
		$recurrence     = ( 'trash' == $post->post_status ) ? 0 : $action->get_schedule();
		$next_timestamp = get_post_time( 'U', true, $post_id );
		$status         = get_post_status( $post_id );

		switch ( $column_name ) {
			case 'hook':

				echo $action_title;

				$actions = array();

				if ( current_user_can( 'edit_post', $post->ID ) && ! in_array( $post->post_status, array( 'publish', 'in-progress', 'trash' ) ) ) {
					$actions['process'] = "<a title='" . esc_attr( __( 'Process the action now as if it were run as part of a queue' ) ) . "' href='" . self::get_run_action_link( $post->ID, 'process' ) . "'>" . __( 'Run', 'action-scheduler' ) . "</a>";
				}

				if ( current_user_can( 'delete_post', $post->ID ) ) {
					if ( 'trash' == $post->post_status ) {
						$post_type_object = get_post_type_object( $post->post_type );
						$actions['untrash'] = "<a title='" . esc_attr( __( 'Restore this action from the Trash' ) ) . "' href='" . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ) . "'>" . __( 'Restore', 'action-scheduler' ) . "</a>";
					} elseif ( EMPTY_TRASH_DAYS ) {
						$actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move this action to the Trash' ) ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . __( 'Trash', 'action-scheduler' ) . "</a>";
					}

					if ( 'trash' == $post->post_status || !EMPTY_TRASH_DAYS ) {
						$actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete this action permanently' ) ) . "' href='" . get_delete_post_link( $post->ID, '', true ) . "'>" . __( 'Delete Permanently', 'action-scheduler' ) . "</a>";
					}
				}

				$action_count = count( $actions );
				$i = 0;

				echo '<div class="row-actions">';
				foreach ( $actions as $a => $link ) {
					++$i;
					( $i == $action_count ) ? $sep = '' : $sep = ' | ';
					echo "<span class='$a'>$link$sep</span>";
				}
				echo '</div>';

				break;
			case 'status':
				if ( 'publish' == $status ) {
					_e( 'Complete', 'action-scheduler' );
				} else {
					echo ucfirst( $status );
				}
				break;
			case 'args':
				$action_args = ( 'trash' == $post->post_status ) ? $post->post_content : $action->get_args();
				if ( is_array( $action_args ) ) {
					foreach( $action_args as $key => $value ) {
						printf( "<code>%s => %s</code><br/>", $key, $value );
					}
				}
				break;
			case 'recurrence':
				if ( method_exists( $recurrence, 'interval_in_seconds' ) ) {
					echo self::human_interval( $recurrence->interval_in_seconds() );
				} else {
					_e( 'Non-repeating', 'action-scheduler' );
				}
				break;
			case 'scheduled':
				echo get_date_from_gmt( date( 'Y-m-d H:i:s', $next_timestamp ), 'Y-m-d H:i:s' );
				if ( gmdate( 'U' ) > $next_timestamp ) {
					printf( __( ' (%s ago)', 'action-scheduler' ), human_time_diff( gmdate( 'U' ), $next_timestamp ) );
				} else {
					echo ' (' . human_time_diff( gmdate( 'U' ), $next_timestamp ) . ')';
				}
				break;
			case 'modified':
				echo get_post_modified_time( 'Y-m-d H:i:s' );
				$modified_timestamp = get_post_modified_time( 'U', true );
				if ( gmdate( 'U' ) > $modified_timestamp ) {
					printf( __( ' (%s ago)', 'action-scheduler' ), human_time_diff( gmdate( 'U' ), $modified_timestamp ) );
				} else {
					echo ' (' . human_time_diff( gmdate( 'U' ), $modified_timestamp ) . ')';
				}
				break;
			case 'claim':
				echo $post->post_password;
				break;
		}
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

		if ( ActionScheduler_wpPostStore::POST_TYPE == $post->post_type && isset( $actions['edit'] ) ) {
			unset( $actions['edit'] );
		}

		return $actions;
	}

	/**
	 * Retrieve a URI to execute a scheduled action.
	 *
	 * @param int $action_id The ID for a 'scheduled-action' post.
	 * @param string $operation To run the action (including trigger before/after hooks), log the execution and update the action's status, use 'process', to simply trigger the action, use 'execute'. Default 'execute'.
	 * @return string The URL for running the action.
	 */
	private static function get_run_action_link( $action_id, $operation = 'process' ) {

		if ( !$post = get_post( $action_id ) )
			return;

		$post_type_object = get_post_type_object( $post->post_type );

		if ( ! $post_type_object )
			return;

		if ( ! current_user_can( 'edit_post', $post->ID ) )
			return;

		$execute_link = add_query_arg( array( 'action' => $operation, 'post_id' => $post->ID ), self::$admin_url );

		return wp_nonce_url( $execute_link, "{$operation}-action_{$post->ID}" );
	}

	/**
	 * Run an action when triggered from the Action Scheduler administration screen.
	 *
	 * @codeCoverageIgnore
	 */
	public static function maybe_execute_action() {

		if ( ! isset( $_GET['action'] ) || 'process' != $_GET['action'] || ! isset( $_GET['post_id'] ) ){
			return;
		}

		$action_id = absint( $_GET['post_id'] );

		check_admin_referer( $_GET['action'] . '-action_' . $action_id );

		try {
			ActionScheduler::runner()->process_action( $action_id );
			$success = 1;
		} catch ( Exception $e ) {
			$success = 0;
		}

		wp_redirect( add_query_arg( array( 'executed' => $success, 'ids' => $action_id ), self::$admin_url ) );
		exit();
	}

	/**
	 * Convert an interval of seconds into a two part human friendly string.
	 *
	 * The WordPress human_time_diff() function only calculates the time difference to one degree, meaning
	 * even if an action is 1 day and 11 hours away, it will display "1 day". This funciton goes one step
	 * further to display two degrees of accuracy.
	 *
	 * Based on Crontrol::interval() funciton by Edward Dale: https://wordpress.org/plugins/wp-crontrol/
	 *
	 * @param int $interval A interval in seconds.
	 * @return string A human friendly string representation of the interval.
	 */
	public static function admin_notices() {

		if ( self::is_admin_page() ) {

			if ( ActionScheduler_Store::instance()->get_claim_count() >= apply_filters( 'action_scheduler_queue_runner_concurrent_batches', 5 ) ) : ?>
<div id="message" class="updated">
	<p><?php printf( __( 'Maximum simulatenous batches already in progress (%s queues). No actions will be processed until the current batches are complete.', 'action-scheduler' ), ActionScheduler_Store::instance()->get_claim_count() ); ?></p>
</div>
			<?php endif;

			if ( isset( $_GET['executed'] ) && isset( $_GET['ids'] ) ) {
				$action = ActionScheduler::store()->fetch_action( $_GET['ids'] );
				$action_hook_html = '<strong>' . $action->get_hook() . '</strong>';
				if ( 1 == $_GET['executed'] ) : ?>
<div id="message" class="updated">
	<p><?php printf( __( 'Successfully executed the action: %s', 'action-scheduler' ), $action_hook_html ); ?></p>
</div>
			<?php else : ?>
<div id="message" class="error">
	<p><?php printf( __( 'Could not execute the action: %s', 'action-scheduler' ), $action_hook_html ); ?></p>
</div>
			<?php endif;
			}
		}
	}

	/**
	 * Convert a interval of seconds into a two part human friendly string.
	 *
	 * The WordPress human_time_diff() function only calculates the time difference to one degree, meaning
	 * even if an action is 1 day and 11 hours away, it will display "1 day". This funciton goes one step
	 * further to display two degrees of accuracy.
	 *
	 * Based on Crontrol::interval() funciton by Edward Dale: https://wordpress.org/plugins/wp-crontrol/
	 *
	 * @param int $interval A interval in seconds.
	 * @return string A human friendly string representation of the interval.
	 */
	private static function human_interval( $interval ) {
		// array of time period chunks
		$chunks = array(
			array( 60 * 60 * 24 * 365 , _n_noop( '%s year', '%s years', 'action-scheduler' ) ),
			array( 60 * 60 * 24 * 30 , _n_noop( '%s month', '%s months', 'action-scheduler' ) ),
			array( 60 * 60 * 24 * 7, _n_noop( '%s week', '%s weeks', 'action-scheduler' ) ),
			array( 60 * 60 * 24 , _n_noop( '%s day', '%s days', 'action-scheduler' ) ),
			array( 60 * 60 , _n_noop( '%s hour', '%s hours', 'action-scheduler' ) ),
			array( 60 , _n_noop( '%s minute', '%s minutes', 'action-scheduler' ) ),
			array(  1 , _n_noop( '%s second', '%s seconds', 'action-scheduler' ) ),
		);

		if ( $interval <= 0 ) {
			return __( 'Now!', 'action-scheduler' );
		}

		// Step one: the first chunk
		for ( $i = 0, $j = count( $chunks ); $i < $j; $i++ ) {
			$seconds = $chunks[$i][0];
			$name = $chunks[$i][1];

			if ( ( $count = floor( $interval / $seconds ) ) != 0 ) {
				break;
			}
		}

		$output = sprintf( _n( $name[0], $name[1], $count, 'action-scheduler' ), $count );

		if ( $i + 1 < $j ) {
			$seconds2 = $chunks[$i + 1][0];
			$name2 = $chunks[$i + 1][1];

			if ( ( $count2 = floor( ( $interval - ( $seconds * $count ) ) / $seconds2 ) ) != 0 ) {
				// add to output var
				$output .= ' '.sprintf( _n( $name2[0], $name2[1], $count2, 'action-scheduler' ), $count2 );
			}
		}

		return $output;
	}

	/**
	 * Filter search queries to allow searching by Claim ID (i.e. post_password).
	 *
	 * @param string $orderby MySQL orderby string.
	 * @param WP_Query $query Instance of a WP_Query object
	 * @return string MySQL orderby string.
	 */
	public function custom_orderby( $orderby, $query ){
		global $wpdb;

		if ( self::is_admin_page() && ! empty( $query->query['orderby'] ) && 'post_password' == $query->query['orderby'] ) {
			$orderby = "$wpdb->posts.post_password " . $query->query['order'];
		}

		return $orderby;
	}

	/**
	 * Filter search queries to allow searching by Claim ID (i.e. post_password).
	 *
	 * @param string $search MySQL search string.
	 * @param WP_Query $query Instance of a WP_Query object
	 * @return string MySQL search string.
	 */
	public function search_post_password( $search, $query ) {
		global $wpdb;

		if ( self::is_admin_page() && ! empty( $search ) ) {

			$search = '';

			$searchand = '';
			$n = ! empty( $query->query_vars['exact'] ) ? '' : '%';
			foreach ( $query->query_vars['search_terms'] as $term ) {
				$term = $wpdb->esc_like( esc_sql( $term ) );
				$search .= "{$searchand}(($wpdb->posts.post_title LIKE '{$n}{$term}{$n}') OR ($wpdb->posts.post_content LIKE '{$n}{$term}{$n}') OR ($wpdb->posts.post_password LIKE '{$n}{$term}{$n}'))";
				$searchand = ' AND ';
			}

			if ( ! empty( $search ) ) {
				$search = " AND ({$search}) ";
			}

		}

		return $search;
	}

	/**
	 * Change messages when a scheduled action is updated.
	 *
	 * @param  array $messages
	 * @return array
	 */
	public function post_updated_messages( $messages ) {
		global $post, $post_ID;

		$messages[ ActionScheduler_wpPostStore::POST_TYPE ] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Action updated.', 'action-scheduler' ),
			2  => __( 'Custom field updated.', 'action-scheduler' ),
			3  => __( 'Custom field deleted.', 'action-scheduler' ),
			4  => __( 'Action updated.', 'action-scheduler' ),
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Action restored to revision from %s', 'action-scheduler' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Action scheduled.', 'action-scheduler' ),
			7  => __( 'Action saved.', 'action-scheduler' ),
			8  => __( 'Action submitted.', 'action-scheduler' ),
			9  => sprintf( __( 'Action scheduled for: <strong>%1$s</strong>', 'action-scheduler' ), date_i18n( __( 'M j, Y @ G:i', 'action-scheduler' ), strtotime( $post->post_date ) ) ),
			10 => __( 'Action draft updated.', 'action-scheduler' ),
		);

		return $messages;
	}

	/**
	 * Check if the current request is for the Schedul Actions administration screen.
	 *
	 * @return bool
	 */
	private static function is_admin_page() {
		if ( is_admin() && isset( $_GET['post_type'] ) && $_GET['post_type'] == ActionScheduler_wpPostStore::POST_TYPE ) {
			return true;
		} else {
			return false;
		}
	}
}