<?php

/**
 *
 */
class ActionScheduler_ListTable extends PP_List_Table {
	/**
	 * The package name. It is used also as the domain for the translations
	 */
	protected $package = 'action-scheduler';

	/**
	 * Columns to show (name => label). The label is automatically
	 * translated before rendering
	 */
	protected $columns = array(
		'hook'   => 'Hook',
		'status' => 'Status',
		'args'   => 'Arguments',
		'group'  => 'Group',
		'recurrence' => 'Recurrence',
		'scheduled'  => 'Scheduled Date',
		'claim_id'   => 'Claim ID',
		'comments'   => 'Log',
	);

	protected $row_actions = array(
		'hook' => array(
			'run' => array( 'Run', 'Process the action now as if it were run as part of a queue' ),
		),
	);

	/**
	 *  The active data stores
	 */
	protected $store;

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
	 * Set the current data store object into `store->action` and initialises the object.
	 */
	public function __construct() {
		$this->store = (object) array(
			'action' => ActionScheduler_Store::instance(),
			'log'    => ActionScheduler_Logger::instance(),
		);

		parent::__construct( array(
			'singular' => $this->translate( 'action-scheduler' ),
			'plural'   => $this->translate( 'action-scheduler' ),
			'ajax'     => false,
		) );
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

	public function column_comments( array $row ) {
		echo '<div class="post-com-count-wrapper">';
		echo '<a href="" class="post-com-count post-com-count-approved">';
		echo '<span class="comment-count-approved">' . esc_html( $row['comments'] ) . '</span>';
		echo '</a>';
		echo '</div>';
	}

	/**
	 * Returns the scheduled date in a human friendly format.
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
			return 'Completed';
		}

		return 'Pending';
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
			$this->store->action->delete_action( $id );
		}
	}

	/**
	 * Implements the logic behind running an action. PP_Table_List validates the request and their
	 * parameters are valid.
	 */
	protected function row_action_run( $row_id ) {
		$action = $this->store->action->fetch_action( $row_id );
		$action->execute();
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
		);

		$this->items = array();

		$total_items = $this->store->action->query_actions_count( $query );

		foreach ( $this->store->action->query_actions( $query ) as $id ) {
			$item = $this->store->action->fetch_action( $id );
			$this->items[ $id ] = array(
				'ID'     => $id,
				'hook'   => $item->get_hook(),
				'status' => $this->get_status( $item ),
				'args'   => $item->get_args(),
				'group'  => $item->get_group(),
				'recurrence' => $this->get_recurrence( $item ),
				'scheduled'  => $item->get_schedule(),
				'comments'   => count( $this->store->log->get_logs( $id ) ),
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
			$total_items = $this->store->action->query_actions_count( compact( 'status' ) );
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
