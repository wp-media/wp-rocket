<?php

class ActionScheduler_ListTable extends PP_List_Table {
	protected $package = 'action-scheduler';

	protected $columns = array(
		'hook' => 'Hook',
		'updated_at' => 'Date',
	);

	protected $data_store;

	public function __construct() {
		$this->data_store = ActionScheduler_Store::instance();
		parent::__construct( array(
			'singular' => $this->translate( 'action-scheduler' ),
			'plural'   => $this->translate( 'action-scheduler' ),
			'ajax'     => false,
		) );
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
	 * {@inheritDoc}
	 */
	public function prepare_items() {
		$this->process_bulk_action();

		if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
			// _wp_http_referer is used only on bulk actions, we remove it to keep the $_GET shorter
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
			exit;
		}

		$this->prepare_column_headers();

		$query = array(
			'per_page' => $this->get_items_query_limit(),
			'offset'   => $this->get_items_query_offset(),
		);

		$this->items = array();
		foreach ( $this->data_store->query_actions( $query ) as $id ) {
			$item = $this->data_store->fetch_action( $id );
			$this->items[ $id ] = array(
				'hook' => $item->get_hook(),
			);
		}
	}
}
