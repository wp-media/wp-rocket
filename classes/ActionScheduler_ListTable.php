<?php

/**
 * Implements the admin view of the actions.
 */
class ActionScheduler_ListTable extends PP_List_Table {
	/**
	 * The package name. It is used also as the domain for the translations
	 */
	protected $package = 'action-scheduler';

	/**
	 * Columns to show (name => label).
	 */
	protected $columns = array();

	protected $row_actions = array(
		'hook' => array(
			'run' => array( 'Run', 'Process the action now as if it were run as part of a queue' ),
		),
	);

	/**
	 *  The active data stores
	 */
	protected $stores;

	/**
	 * Bulk actions. The key of the array is the method name of the implementation:
	 *
	 *     bulk_<key>(array $ids, string $sql_in).
	 *
	 * See the comments in the parent class for further details
	 */
	protected $bulk_actions = array(
		'delete' => 'Delete',
	);


	/**
	 * If it is true it will load our own javascript library.
	 *
	 * Our javascript library will show the logs as a jQuery modal.
	 */
	protected static $should_include_js = false;


	/**
	 * Sets the current data store object into `store->action` and initialises the object.
	 */
	public function __construct() {
		self::$should_include_js = true;

		$this->columns = array(
			'hook'   => __( 'Hook', 'action-scheduler' ),
			'status' => __( 'Status', 'action-scheduler' ),
			'args'   => __( 'Arguments', 'action-scheduler' ),
			'group'  => __( 'Group', 'action-scheduler' ),
			'recurrence' => __( 'Recurrence', 'action-scheduler' ),
			'scheduled'  => __( 'Scheduled Date', 'action-scheduler' ),
			'claim_id'   => __( 'Claim ID', 'action-scheduler' ),
			'comments'   => __( 'Log', 'action-scheduler' ),
		);

		$this->row_actions = array(
			'hook' => array(
				'run' => array(
					__( 'Run', 'action-scheduler' ),
					__( 'Process the action now as if it were run as part of a queue', 'action-scheduler' ),
				),
			),
		);

		$this->stores = (object) array(
			'action' => ActionScheduler_Store::instance(),
			'log'    => ActionScheduler_Logger::instance(),
		);

		parent::__construct( array(
			'singular' => __( 'action-scheduler', 'action-scheduler' ),
			'plural'   => __( 'action-scheduler', 'action-scheduler' ),
			'ajax'     => false,
		) );
	}

	/**
	 * Sets up the class. It is executed if `is_admin()` is TRUE.
	 */
	public static function init() {
		add_action( 'admin_footer', __CLASS__ . '::maybe_include_assets' );
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::register_javascript' );
	}

	/**
	 * Include our assets files if needed.
	 *
	 * Instead of loading our javascript everywhere in the WP-Admin, this method will make sure
	 * our assets files are loaded only if this class is constructed.
	 *
	 * This method will be called in the admin_footer and it will print the CSS we need right away
	 * and will queue our javascript file.
	 *
	 * Our javascript/css does the following things:
	 *   1. Opens the logs in a modal.
	 *   2. Loads the CSS for jquery-ui (used in the modals):
	 */
	public static function maybe_include_assets() {
		if ( ! self::$should_include_js ) {
			return;
		}
		wp_enqueue_script( 'action-scheduler' );
		wp_print_styles( 'wp-jquery-ui-dialog' );
	}

	/**
	 * Registers our javascript library and its dependencies.
	 */
	public static function register_javascript() {
		wp_register_script( 'action-scheduler', plugins_url( 'action-scheduler.js', dirname( __FILE__ ) ), array(
			'jquery',
			'jquery-ui-core',
			'jquery-ui-dialog',
		), '1.0', true );
	}

	/**
	 * Returns the recurrence of an action or 'Non-repeating'. The output is human readable.
	 *
	 * @param ActionScheduler_Action $item
	 *
	 * @return string
	 */
	protected function get_recurrence( $item ) {
		$recurrence = $item->get_schedule();
		if ( method_exists( $recurrence, 'interval_in_seconds' ) ) {
			return self::human_interval( $recurrence->interval_in_seconds() );
		}
		return __( 'Non-repeating', 'action-scheduler' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_items_query_limit() {
		return $this->get_items_per_page( $this->package . '_items_per_page', $this->items_per_page );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_items_query_offset() {
		global $wpdb;

		$per_page = $this->get_items_query_limit();
		$current_page = $this->get_pagenum();
		if ( 1 < $current_page ) {
			return $per_page * ( $current_page - 1 );
		}

		return 0;
	}

	/**
	 * Serializes the argument of an action to render it in a human friendly format.
	 *
	 * @param array $row The array representation of the current row of the table
	 *
	 * @return string
	 */
	public function column_args( array $row ) {
		return '<code>' . json_encode( $row['args'] ) . '</code>';
	}

	/**
	 * Prints the comments, which are the log entries. It needs to be named comments otherwise it won't pickup
	 * the WordPress styles to make the number pretty.
	 *
	 * It will also render all the log entries, but it will be hidden and rendered on click in the number as a modal.
	 *
	 * @param array $row Action array.
	 */
	public function column_comments( array $row ) {
		echo '<div id="log-' . $row['ID'] . '" class="log-modal hidden" style="max-width:800px">';
		echo '<h3>' . esc_html( sprintf( __( 'Log entries for %d', 'action-scheduler' ),  $row['ID'] ) ) . '</h3>';
		$timezone = new DateTimezone( 'UTC' );
		foreach ( $row['comments'] as $log ) {
			$date = $log->get_date();
			$date->setTimezone( $timezone );
			echo '<p><strong>' . esc_html( $date->format( 'Y-m-d H:i:s' ) ) . ' UTC</strong> ' . esc_html( $log->get_message() ) . '</p>';
		}
		echo '</div>';

		echo '<div class="post-com-count-wrapper">';
		echo '<a href="#" data-log-id="' . $row['ID'] . '" class="post-com-count post-com-count-approved log-modal-open">';
		echo '<span class="comment-count-approved">' . esc_html( count( $row['comments'] ) ) . '</span>';
		echo '</a>';
		echo '</div>';
	}

	/**
	 * Prints the scheduled date in a human friendly format.
	 *
	 * @param array $row The array representation of the current row of the table
	 */
	public function column_scheduled( $row ) {
		$next_timestamp = $row['scheduled']->next()->format( 'U' );
		echo get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $next_timestamp ), 'Y-m-d H:i:s' );
		if ( gmdate( 'U' ) > $next_timestamp ) {
			printf( __( ' (%s ago)', 'action-scheduler' ), human_time_diff( gmdate( 'U' ), $next_timestamp ) );
		} else {
			echo ' (' . human_time_diff( gmdate( 'U' ), $next_timestamp ) . ')';
		}
	}

	/**
	 * Returns if the current action is finished or pending.
	 *
	 * @param ActionScheduler_Action $item
	 */
	protected function get_status( $item ) {
		if ( $item->is_finished() ) {
			return __( 'Completed', 'action-scheduler' );
		}

		return __( 'Pending', 'action-scheduler' );
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
			$this->stores->action->delete_action( $id );
		}
	}

	/**
	 * Implements the logic behind running an action. PP_Table_List validates the request and their
	 * parameters are valid.
	 */
	protected function row_action_run( $action_id ) {
		try {
			ActionScheduler::runner()->process_action( $action_id );
			$success = 1;
		} catch ( Exception $e ) {
			$success = 0;
		}

		set_transient( 'actionscheduler_admin_executed', compact( 'action_id', 'success' ), 30 );
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

		$per_page = $this->get_items_query_limit();
		$query = array(
			'per_page' => $per_page,
			'offset'   => $this->get_items_query_offset(),
			'status'   => $this->get_request_status(),
			'orderby'  => 'modified',
			'order'    => 'ASC',
		);

		$this->items = array();

		$total_items = $this->stores->action->query_actions_count( $query );

		foreach ( $this->stores->action->query_actions( $query ) as $id ) {
			$item = $this->stores->action->fetch_action( $id );
			$this->items[ $id ] = array(
				'ID'     => $id,
				'hook'   => $item->get_hook(),
				'status' => $this->get_status( $item ),
				'args'   => $item->get_args(),
				'group'  => $item->get_group(),
				'recurrence' => $this->get_recurrence( $item ),
				'scheduled'  => $item->get_schedule(),
				'claim_id'   => '',
				'comments'   => $this->stores->log->get_logs( $id ),
			);
		}

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}

	/**
	 * This function is used to filter the actions by a status. It will return the status based on a 
	 * a GET parameter or it will filter by 'pending' by default. 
	 * We filter by status because if we do not filter we have NullActions that are not rendered at the 
	 * moment.
	 *
	 * @return string
	 */
	public function get_request_status() {
		$statuses = array(
			ActionScheduler_Store::STATUS_PENDING,
			ActionScheduler_Store::STATUS_COMPLETE,
			ActionScheduler_Store::STATUS_FAILED,
		);

		if ( ! empty( $_GET['status'] ) && in_array( $_GET['status'], $statuses ) ) {
			return $_GET['status'];
		}

		return ActionScheduler_Store::STATUS_PENDING;
	}

	/**
	 * Prints the available statuses so the user can click to filter.
	 *
	 * @return void
	 */
	public function display_filter_by_status() {
		$statuses = array(
			'pending' => ActionScheduler_Store::STATUS_PENDING,
			'complete' => ActionScheduler_Store::STATUS_COMPLETE,
			'failed'  => ActionScheduler_Store::STATUS_FAILED,
		);

		$li = array();
		foreach ( $statuses as $name => $status ) {
			$total_items = $this->stores->action->query_actions_count( compact( 'status' ) );
			if ( 0 === $total_items ) {
				continue;
			}

			if ( $status === $this->get_request_status() ) {
				$li[] =  '<li class="' . esc_attr( $name ) . '">'
					. '<strong>'
						. esc_html( ucfirst( $name ) )
					. "</strong> ($total_items)"
				. '</li>';
				continue;
			}

			$li[] =  '<li class="' . esc_attr( $name ) . '">'
				. '<a href="' . esc_url( add_query_arg( 'status', $status ) )  . '">'
				. esc_html( ucfirst( $name ) )
				. "</a> ($total_items)"
			. '</li>';
		}

		if ( $li ) {
			echo '<ul class="subsubsub">';
			echo implode( " | \n", $li );
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
