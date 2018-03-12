<?php

/**
 * Implements the admin view of the actions.
 */
class ActionScheduler_ListTable extends PP_List_Table {
	/**
	 * The package name.
	 */
	protected $package = 'action-scheduler';

	/**
	 * Columns to show (name => label).
	 */
	protected $columns = array();

	/**
	 * Actions (name => label).
	 */
	protected $row_actions = array();

	/**
	 *  The active data stores
	 */
	protected $store;

	/**
	 *  A logger to use for getting action logs to display
	 */
	protected $logger;

	/**
	 * Bulk actions. The key of the array is the method name of the implementation:
	 *
	 *     bulk_<key>(array $ids, string $sql_in).
	 *
	 * See the comments in the parent class for further details
	 */
	protected $bulk_actions = array();

	/**
	 * Flag variable to render our notifications, if any, once.
	 */
	protected static $did_notification = false;

	/**
	 * Array of seconds for common time periods, like week or month, alongside an internationalised string representation, i.e. "Day" or "Days"
	 */
	private static $time_periods;

	/**
	 * Sets the current data store object into `store->action` and initialises the object.
	 */
	public function __construct( $store, $logger ) {

		$this->store  = $store;
		$this->logger = $logger;

		$request_status = $this->get_request_status();

		$this->maybe_render_admin_notices();

		$this->bulk_actions = array(
			'delete' => __( 'Delete', 'action-scheduler' ),
		);

		$this->columns = array(
			'hook'   => __( 'Hook', 'action-scheduler' ),
			'status' => __( 'Status', 'action-scheduler' ),
			'args'   => __( 'Arguments', 'action-scheduler' ),
			'group'  => __( 'Group', 'action-scheduler' ),
			'recurrence'  => __( 'Recurrence', 'action-scheduler' ),
			'schedule'    => __( 'Scheduled Date', 'action-scheduler' ),
			'log_entries' => __( 'Log', 'action-scheduler' ),
		);

		if ( in_array( $request_status, array( 'in-progress', 'failed' ) ) ) {
			$this->columns += array( 'claim_id' => __( 'Claim ID', 'action-scheduler' ) );
		}

		$this->sort_by = array(
			'schedule',
			'hook',
			'group',
		);

		if ( 'all' === $request_status ) {
			$this->sort_by[] = 'status';
		}

		$this->row_actions = array(
			'hook' => array(
				'run' => array(
					__( 'Run', 'action-scheduler' ),
					__( 'Process the action now as if it were run as part of a queue', 'action-scheduler' ),
				),
			),
		);

		self::$time_periods = array(
			array(
				'seconds' => YEAR_IN_SECONDS,
				'names'   => _n_noop( '%s year', '%s years', 'action-scheduler' ),
			),
			array(
				'seconds' => MONTH_IN_SECONDS,
				'names'   => _n_noop( '%s month', '%s months', 'action-scheduler' ),
			),
			array(
				'seconds' => WEEK_IN_SECONDS,
				'names'   => _n_noop( '%s week', '%s weeks', 'action-scheduler' ),
			),
			array(
				'seconds' => DAY_IN_SECONDS,
				'names'   => _n_noop( '%s day', '%s days', 'action-scheduler' ),
			),
			array(
				'seconds' => HOUR_IN_SECONDS,
				'names'   => _n_noop( '%s hour', '%s hours', 'action-scheduler' ),
			),
			array(
				'seconds' => MINUTE_IN_SECONDS,
				'names'   => _n_noop( '%s minute', '%s minutes', 'action-scheduler' ),
			),
			array(
				'seconds' => 1,
				'names'   => _n_noop( '%s second', '%s seconds', 'action-scheduler' ),
			),
		);

		parent::__construct( array(
			'singular' => __( 'action-scheduler', 'action-scheduler' ),
			'plural'   => __( 'action-scheduler', 'action-scheduler' ),
			'ajax'     => false,
		) );
	}

	/**
	 * Convert a interval of seconds into a two part human friendly string.
	 *
	 * The WordPress human_time_diff() function only calculates the time difference to one degree, meaning
	 * even if an action is 1 day and 11 hours away, it will display "1 day". This funciton goes one step
	 * further to display two degrees of accuracy.
	 *
	 * Inspired by the Crontrol::interval() function by Edward Dale: https://wordpress.org/plugins/wp-crontrol/
	 *
	 * @param int $interval A interval in seconds.
	 * @return string A human friendly string representation of the interval.
	 */
	private static function human_interval( $interval, $periods_to_include = 2 ) {

		if ( $interval <= 0 ) {
			return __( 'Now!', 'action-scheduler' );
		}

		$output = '';

		for ( $time_period_index = 0, $periods_in_interval = 0, $periods_included = 0, $seconds_remaining = $interval; $time_period_index < count( self::$time_periods ) && $seconds_remaining > 0 && $periods_included < $periods_to_include; $time_period_index++ ) {

			$periods_in_interval = floor( $seconds_remaining / self::$time_periods[ $time_period_index ]['seconds'] );

			if ( $periods_in_interval > 0 ) {
				if ( ! empty( $output ) ) {
					$output .= ' ';
				}
				$output .= sprintf( _n( self::$time_periods[ $time_period_index ]['names'][0], self::$time_periods[ $time_period_index ]['names'][1], $periods_in_interval, 'action-scheduler' ), $periods_in_interval );
				$seconds_remaining -= $periods_in_interval * self::$time_periods[ $time_period_index ]['seconds'];
				$periods_included++;
			}
		}

		return $output;
	}

	/**
	 * Returns the recurrence of an action or 'Non-repeating'. The output is human readable.
	 *
	 * @param ActionScheduler_Action $action
	 *
	 * @return string
	 */
	protected function get_recurrence( $action ) {
		if ( $action->get_schedule()->is_recurring() ) {
			return sprintf( __( 'Every %s', 'action-scheduler' ), self::human_interval( $recurrence->interval_in_seconds() ) );
		}
		return __( 'Non-repeating', 'action-scheduler' );
	}

	/**
	 * Serializes the argument of an action to render it in a human friendly format.
	 *
	 * @param array $row The array representation of the current row of the table
	 *
	 * @return string
	 */
	public function column_args( array $row ) {
		if ( empty( $row['args'] ) ) {
			return;
		}

		$row_html = '<ul>';
		foreach ( $row['args'] as $key => $value ) {
			$row_html .= sprintf( '<li><code>%s => %s</li></code>', $key, $value );
		}
		$row_html .= '</ul>';

		return $row_html;
	}

	/**
	 * Prints the logs entries inline. We do so to avoid loading Javascript and other hacks to show it in a modal.
	 *
	 * @param array $row Action array.
	 */
	public function column_log_entries( array $row ) {
		echo '<ol>';
		$timezone = new DateTimezone( 'UTC' );
		foreach ( $row['log_entries'] as $log_entry ) {
			$this->print_log_entry( $log_entry, $timezone );
		}
		echo '</ol>';
	}

	/**
	 * Prints the logs entries inline. We do so to avoid loading Javascript and other hacks to show it in a modal.
	 *
	 * @param array $row Action array.
	 */
	protected function print_log_entry( ActionScheduler_LogEntry $log_entry, DateTimezone $timezone ) {
		$date = $log_entry->get_date();
		$date->setTimezone( $timezone );
		echo '<li><strong>' . esc_html( $date->format( 'Y-m-d H:i:s e' ) ) . '</strong><br/>' . esc_html( $log_entry->get_message() ) . '</li>';
	}

	/**
	 * Renders admin notifications
	 *
	 * Notifications:
	 *  1. When the maximum number of tasks are being executed simultaneously
	 *  2. Notifications when a task us manually executed
	 */
	public function maybe_render_admin_notices() {
		if ( self::$did_notification ) {
			return;
		}

		self::$did_notification = true;

		if ( $this->store->get_claim_count() >= ActionScheduler::runner()->get_concurrent_batch_count() ) : ?>
<div id="message" class="updated">
	<p><?php printf( __( 'Maximum simulatenous batches already in progress (%s queues). No actions will be processed until the current batches are complete.', 'action-scheduler' ), $this->store->get_claim_count() ); ?></p>
</div>
		<?php endif;
		$notification = get_transient( 'actionscheduler_admin_executed' );
		if ( is_array( $notification ) ) {
			delete_transient( 'actionscheduler_admin_executed' );

			$action = $this->store->fetch_action( $notification['action_id'] );
			$action_hook_html = '<strong><code>' . $action->get_hook() . '</code></strong>';
			if ( 1 == $notification['success'] ): ?>
				<div id="message" class="updated">
					<p><?php printf( __( 'Successfully executed action: %s', 'action-scheduler' ), $action_hook_html ); ?></p>
				</div>
			<?php else : ?>
			<div id="message" class="error">
				<p><?php printf( __( 'Could not execute action: "%s". Error: %s', 'action-scheduler' ), $action_hook_html, esc_html( $notification['action_id'] ) ); ?></p>
			</div>
			<?php endif;
		}
	}


	/**
	 * Prints the scheduled date in a human friendly format.
	 *
	 * @param array $row The array representation of the current row of the table
	 */
	public function column_schedule( $row ) {
		$this->print_next_scheduled( $row['schedule'] );
	}

	/**
	 * Prints the scheduled date in a human friendly format.
	 *
	 * @param ActionScheduler_Schedule $schedule
	 */
	protected function print_next_scheduled( ActionScheduler_Schedule $schedule ) {

		if ( ! $schedule->next() ) {
			return;
		}

		echo $schedule->next()->format( 'Y-m-d H:i:s e' );

		$next_timestamp = $schedule->next()->format( 'U' );

		echo '<br/>';

		if ( gmdate( 'U' ) > $next_timestamp ) {
			printf( __( ' (%s ago)', 'action-scheduler' ), self::human_interval( gmdate( 'U' ) - $next_timestamp ) );
		} else {
			echo ' (' . self::human_interval( $next_timestamp - gmdate( 'U' ) ) . ')';
		}
	}

	/**
	 * Bulk delete
	 *
	 * Deletes actions based on their ID. This is the handler for the bulk delete. It assumes the data
	 * properly validated by the callee and it will delete the actions without any extra validation.
	 *
	 * @param array $ids
	 *
	 * @return void
	 */
	protected function bulk_delete( array $ids, $ids_sql ) {
		foreach ( $ids as $id ) {
			$this->store->delete_action( $id );
		}
	}

	/**
	 * Implements the logic behind running an action. PP_Table_List validates the request and their
	 * parameters are valid.
	 *
	 * @param int $action_id
	 */
	protected function row_action_run( $action_id ) {
		try {
			ActionScheduler::runner()->process_action( $action_id );
			$success = 1;
			$error_message = '';
		} catch ( Exception $e ) {
			$success = 0;
			$error_message = $e->getMessage();
		}

		set_transient( 'actionscheduler_admin_executed', compact( 'action_id', 'success', 'error_message' ), 30 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepare_items() {
		$this->process_bulk_action();

		$this->process_row_actions();

		if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
			// _wp_http_referer is used only on bulk actions, we remove it to keep the $_GET shorter
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
			exit;
		}

		$this->prepare_column_headers();

		$per_page = $this->get_items_per_page( $this->package . '_items_per_page', $this->items_per_page );
		$query = array(
			'per_page' => $per_page,
			'offset'   => $this->get_items_offset(),
			'status'   => $this->get_request_status(),
			'orderby'  => $this->get_request_orderby(),
			'order'    => $this->get_request_order(),
		);

		$this->items = array();

		$total_items = $this->store->query_actions( $query, 'count' );

		$status_labels = $this->store->get_status_labels();

		foreach ( $this->store->query_actions( $query ) as $action_id ) {
			$action = $this->store->fetch_action( $action_id );
			$this->items[ $action_id ] = array(
				'ID'     => $action_id,
				'hook'   => $action->get_hook(),
				'status' => $status_labels[ $this->store->get_status( $action_id ) ],
				'args'   => $action->get_args(),
				'group'  => $action->get_group(),
				'log_entries' => $this->logger->get_logs( $action_id ),
				'claim_id'    => $this->store->get_claim_id( $action_id ),
				'recurrence'  => $this->get_recurrence( $action ),
				'schedule'    => $action->get_schedule(),
			);
		}

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}

	/**
	 * Return the status filter for this request, if any, as long as its a valid status for the current datastore.
	 * If it's not, return 'all' to display all actions.
	 *
	 * @return string
	 */
	public function get_request_status() {

		$request_status = parent::get_request_status();

		if ( ! empty( $request_status ) && array_key_exists( $request_status, $this->store->get_status_labels() ) ) {
			return $request_status;
		}

		return 'all';
	}

	/**
	 * Prints the available statuses so the user can click to filter.
	 *
	 * @return void
	 */
	public function display_filter_by_status() {

		$status_list_items = array();

		$action_counts = $this->store->action_counts();
		$action_counts = array( 'all' => array_sum( $action_counts ) ) + $action_counts;

		foreach ( $action_counts as $status_name => $count ) {

			if ( 0 === $count ) {
				continue;
			}

			if ( $status_name === $this->get_request_status() ) {
				$status_list_item = '<li class="%1$s"><strong>%3$s</strong> (%4$d)</li>';
			} else {
				$status_list_item = '<li class="%1$s"><a href="%2$s">%3$s</a> (%4$d)</li>';
			}

			$status_list_items[] = sprintf( $status_list_item, esc_attr( $status_name ), esc_url( add_query_arg( 'status', $status_name ) ), esc_html( ucfirst( $status_name ) ), absint( $count ) );
		}

		if ( $status_list_items ) {
			echo '<ul class="subsubsub">';
			echo implode( " | \n", $status_list_items );
			echo '</ul>';
		}
	}

	/**
	 * Overrides the original display method to print the `display_filter_by_status()`. By overriding
	 * this object it prints all the needed HTML already, making it easy to use from higher layers because
	 * the object is 'self-contained' and 'self-sufficient'.
	 *
	 * @return void
	 */
	public function display() {
		$this->display_filter_by_status();
		parent::display();
	}
}
