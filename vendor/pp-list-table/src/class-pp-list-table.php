<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Prospress List Table class
 *
 * This abstract class enhances WP_List_Table making it ready to use.
 *
 * By extending this class we can focus on describing how our table looks like,
 * which columns needs to be shown, filter, ordered by and more and forget about the details.
 *
 * This class supports:
 *	- Bulk actions
 *	- Search
 *  - Sortable columns
 *  - Automatic translations of the columns
 *
 * @class       PP_List_Table
 * @package     Propspress Utils
 * @subpackage  Propspress Utils
 * @category    Class
 * @since       0.0.1
 */
abstract class PP_List_Table extends WP_List_Table {

	/**
	 * The table name
	 */
	protected $table_name;

	/**
	 * Package name, used in translations
	 */
	protected $package;

	/**
	 * How many items do we render per page?
	 */
	protected $items_per_page = 10;

	/**
	 * Enables search in this table listing. If this array
	 * is empty it means the listing is not searchable.
	 */
	protected $search_by = array();

	/**
	 * Columns to show in the table listing. It is a key => value pair. The
	 * key must much the table column name and the value is the label, which is
	 * automatically translated.
	 */
	protected $columns = array();

	/**
	 * Defines the row-actions. It expects an array where the key
	 * is the column name and the value is an array of actions.
	 *
	 * The array of actions are key => value, where key is the method name
	 * (with the prefix row_action_<key>) and the value is the label
	 * and title.
	 */
	protected $row_actions = array();

	/**
	 * The Primary key of our table
	 */
	protected $ID = 'ID';

	/**
	 * Enables sorting, it expects an array
	 * of columns (the column names are the values)
	 */
	protected $sort_by = array();

	protected $filter_by = array();

	/**
	 * Enables bulk actions. It must be an array where the key is the action name
	 * and the value is the label (which is translated automatically). It is important
	 * to notice that it will check that the method exists (`bulk_$name`) and will throw
	 * an exception if it does not exists.
	 *
	 * This class will automatically check if the current request has a bulk action, will do the
	 * validations and afterwards will execute the bulk method, with two arguments. The first argument
	 * is the array with primary keys, the second argument is a string with a list of the primary keys,
	 * escaped and ready to use (with `IN`).
	 */
	protected $bulk_actions = array();

	/**
	 * Makes translation easier, it basically just wraps
	 * `_x` with some default (the package name)
	 */
	protected function translate( $text, $context = '' ) {
		return _x( $text, $context, $this->package );
	}

	/**
	 * Reads `$this->bulk_actions` and returns an array that WP_List_Table understands. It
	 * also validates that the bulk method handler exists. It throws an exception because
	 * this is a library meant for developers and missing a bulk method is a development-time error.
	 */
	protected function get_bulk_actions() {
		$actions = array();

		foreach ( $this->bulk_actions as $action => $label ) {
			if ( ! is_callable( array( $this, 'bulk_' . $action ) ) ) {
				throw new RuntimeException( "The bulk action $action does not have a callback method" );
			}

			$actions[ $action ] = $this->translate( $label );
		}

		return $actions;
	}

	/**
	 * Checks if the current request has a bulk action. If that is the case it will validate and will
	 * execute the bulk method handler. Regardless if the action is valid or not it will redirect to
	 * the previous page removing the current arguments that makes this request a bulk action.
	 */
	protected function process_bulk_action() {
		global $wpdb;
		// Detect when a bulk action is being triggered.
		$action = $this->current_action();

		if ( ! $action ) {
			return;
		}

		check_admin_referer( 'bulk-' . $this->_args['plural'] );

		$method   = 'bulk_' . $action;
		if ( array_key_exists( $action, $this->bulk_actions ) && is_callable( array( $this, $method ) ) && ! empty( $_GET['ID'] ) && is_array( $_GET['ID'] ) ) {
			$ids_sql = '(' . implode( ',', array_fill( 0, count( $_GET['ID'] ), '%s' ) ) . ')';
			$this->$method( $_GET['ID'], $wpdb->prepare( $ids_sql, $_GET['ID'] ) );
		}

		wp_redirect( remove_query_arg(
			array( '_wp_http_referer', '_wpnonce', 'ID', 'action', 'action2' ),
			wp_unslash( $_SERVER['REQUEST_URI'] )
		) );
		exit;
	}

	/**
	 * Default code for deleting entries. We trust ids_sql because it is
	 * validated already by process_bulk_action()
	 */
	protected function bulk_delete( array $ids, $ids_sql ) {
		global $wpdb;

		$wpdb->query( "DELETE FROM {$this->table_name} WHERE {$this->ID} IN $ids_sql" );
	}

	/**
	 * Prepares the _column_headers property which is used by WP_Table_List at rendering.
	 * It merges the columns and the sortable columns.
	 */
	protected function prepare_column_headers() {
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);
	}

	/**
	 * Reads $this->sort_by and returns the columns name in a format that WP_Table_List
	 * expects
	 */
	public function get_sortable_columns() {
		$sort_by = array();
		foreach ( $this->sort_by as $column ) {
			$sort_by[ $column ] = array( $column, true );
		}
		return $sort_by;
	}

	/**
	 * Returns the columns names for rendering. It adds a checkbox for selecting everything
	 * as the first column
	 */
	public function get_columns() {
		$columns = array_merge(
			array( 'cb' => '<input type="checkbox" />' ),
			array_map( array( $this, 'translate' ), $this->columns )
		);

		return $columns;
	}

	/**
	 * Get prepared LIMIT clause for items query
	 *
	 * @global wpdb $wpdb
	 *
	 * @return string Prepared LIMIT clause for items query.
	 */
	protected function get_items_query_limit() {
		global $wpdb;

		$per_page = $this->get_items_per_page( $this->package . '_items_per_page', $this->items_per_page );
		return $wpdb->prepare( 'LIMIT %d', $per_page );
	}

	/**
	 * Returns the number of items to offset/skip for this current view.
	 *
	 * @return int
	 */
	protected function get_items_offset() {
		$per_page = $this->get_items_per_page( $this->package . '_items_per_page', $this->items_per_page );
		$current_page = $this->get_pagenum();
		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}

		return $offset;
	}

	/**
	 * Get prepared OFFSET clause for items query
	 *
	 * @global wpdb $wpdb
	 *
	 * @return string Prepared OFFSET clause for items query.
	 */
	protected function get_items_query_offset() {
		global $wpdb;

		return $wpdb->prepare( 'OFFSET %d', $this->get_items_offset() );
	}

	/**
	 * Prepares the ORDER BY sql statement. It uses `$this->sort_by` to know which
	 * columns are sortable. This requests validates the orderby $_GET parameter is a valid
	 * column and sortable. It will also use order (ASC|DESC) using DESC by default.
	 */
	protected function get_items_query_order() {
		if ( empty( $this->sort_by ) ) {
			return '';
		}

		$valid_orders = array_values( $this->sort_by );
		if ( ! empty( $_GET['orderby'] ) && in_array( $_GET['orderby'], $valid_orders ) ) {
			$by = wc_clean( $_GET['orderby'] );
		} else {
			$by = $valid_orders[0];
		}

		$by = esc_sql( $by );
		if ( ! empty( $_GET['order'] ) && 'asc' === strtolower( $_GET['order'] ) ) {
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}
		return "ORDER BY {$by} {$order}";
	}

	/**
	 * Process and return the columns name. This is meant for using with SQL, this means it
	 * always includes the primary key.
	 *
	 * @return array
	 */
	protected function get_table_columns() {
		$columns = array_keys( $this->columns );
		if ( ! in_array( $this->ID, $columns ) ) {
			$columns[] = $this->ID;
		}

		return $columns;
	}

	/**
	 * Check if the current request is doing a "full text" search. If that is the case
	 * prepares the SQL to search texts using LIKE.
	 *
	 * If the current request does not have any search or if this list table does not support
	 * that feature it will return an empty string.
	 *
	 * TODO:
	 *   - Improve search doing LIKE by word rather than by phrases.
	 *
	 * @return string
	 */
	protected function get_items_query_search() {
		global $wpdb;

		if ( empty( $_GET['s'] ) || empty( $this->search_by ) ) {
			return '';
		}

		$filter  = array();
		foreach ( $this->search_by as $column ) {
			$filter[] = '`' . $column . '` like "%' . $wpdb->esc_like( $_GET['s'] ) . '%"';
		}
		return implode( ' OR ', $filter );
	}

	/**
	 * Prepares the SQL to filter rows by the options defined at `$this->filter_by`. Before trusting
	 * any data sent by the user it validates that it is a valid option.
	 */
	protected function get_items_query_filters() {
		global $wpdb;

		if ( ! $this->filter_by || empty( $_GET['filter_by'] ) || ! is_array( $_GET['filter_by'] ) ) {
			return '';
		}

		$filter = array();

		foreach ( $this->filter_by as $column => $options ) {
			if ( empty( $_GET['filter_by'][ $column ] ) || empty( $options[ $_GET['filter_by'][ $column ] ] ) ) {
				continue;
			}

			$filter[] = $wpdb->prepare( "`$column` = %s", $_GET['filter_by'][ $column ] );
		}

		return implode( ' AND ', $filter );

	}

	/**
	 * Prepares the data to feed WP_Table_List.
	 *
	 * This has the core for selecting, sorting and filting data. To keep the code simple
	 * its logic is split among many methods (get_items_query_*).
	 *
	 * Beside populating the items this function will also count all the records that matches
	 * the filtering criteria and will do fill the pagination variables.
	 */
	public function prepare_items() {
		global $wpdb;

		$this->process_bulk_action();

		$this->process_row_actions();

		if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
			// _wp_http_referer is used only on bulk actions, we remove it to keep the $_GET shorter
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
			exit;
		}

		$this->prepare_column_headers();

		$limit   = $this->get_items_query_limit();
		$offset  = $this->get_items_query_offset();
		$order   = $this->get_items_query_order();
		$where   = array_filter(array(
			$this->get_items_query_search(),
			$this->get_items_query_filters(),
		));
		$columns = '`' . implode( '`, `', $this->get_table_columns() ) . '`';

		if ( ! empty( $where ) ) {
			$where = 'WHERE ('. implode( ') AND (', $where ) . ')';
		} else {
			$where = '';
		}

		$sql = "SELECT $columns FROM {$this->table_name} {$where} {$order} {$limit} {$offset}";

		$this->set_items( $wpdb->get_results( $sql, ARRAY_A ) );

		$query_count = "SELECT COUNT({$this->ID}) FROM {$this->table_name} {$where}";
		$total_items = $wpdb->get_var( $query_count );
		$per_page    = $this->get_items_per_page( $this->package . '_items_per_page', $this->items_per_page );
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}

	public function extra_tablenav( $which ) {
		if ( ! $this->filter_by || 'top' !== $which ) {
			return;
		}

		echo '<div class="alignleft actions">';

		foreach ( $this->filter_by as $id => $options ) {
			$default = ! empty( $_GET['filter_by'][ $id ] ) ? $_GET['filter_by'][ $id ] : '';
			if ( empty( $options[ $default ] ) ) {
				$default = '';
			}

			echo '<select name="filter_by[' . esc_attr( $id ) . ']" class="first" id="filter-by-' . esc_attr( $id ) . '">';

			foreach ( $options as $value => $label ) {
				echo '<option value="' . esc_attr( $value ) . '" ' . esc_html( $value == $default ? 'selected' : '' )  .'>'
					. esc_html( $this->translate( $label ) )
				. '</option>';
			}

			echo '</select>';
		}

		submit_button( $this->translate( 'Filter' ), '', 'filter_action', false, array( 'id' => 'post-query-submit' ) );
		echo '</div>';
	}

	/**
	 * Set the data for displaying. It will attempt to unserialize (There is a chance that some columns
	 * are serialized). This can be override in child classes for futher data transformation.
	 */
	protected function set_items( array $items ) {
		$this->items = array();
		foreach ( $items as $item ) {
			$this->items[ $item[ $this->ID ] ] = array_map( 'maybe_unserialize', $item );
		}
	}

	/**
	 * Renders the checkbox for each row, this is the first column and it is named ID regardless
	 * of how the primary key is named (to keep the code simpler). The bulk actions will do the proper
	 * name transformation though using `$this->ID`.
	 */
	public function column_cb( $row ) {
		return '<input name="ID[]" type="checkbox" value="' . esc_attr( $row[ $this->ID ] ) .'" />';
	}

	/**
	 * Renders the row-actions.
	 *
	 * This method renders the action menu, it reads the definition from the $row_actions property,
	 * and it checks that the row action method exists before rendering it.
	 *
	 * @param array $row     Row to render
	 * @param $column_name   Current row
	 */
	protected function maybe_render_actions( $row, $column_name ) {
		if ( empty( $this->row_actions[ $column_name ] ) ) {
			return;
		}

		$row_id = $row[ $this->ID ];

		echo '<div class="row-actions">';
		foreach ( $this->row_actions[ $column_name ] as $action => $definition ) {
			if ( is_array( $definition ) ) {
				list( $title, $label ) = $definition;
			} else {
				$title = $label = $definition;
			}

			if ( ! method_exists( $this, 'row_action_' . $action ) ) {
				continue;
			}

			echo '<span class="' . esc_attr( $action ) . '">';
			echo '<a href="' . add_query_arg( array(
				'row_action' => $action,
				'row_id' => $row_id,
				'nonce'  => wp_create_nonce( $action . '::' . $row_id ),
			) ) . '" title="' . esc_attr( $this->translate( $label ) ) . '">' . esc_html( $this->translate( $title ) ) . '</a>';
			echo '</span>';
		}
		echo '</div>';
	}

	protected function process_row_actions() {
		$parameters = array( 'row_action', 'row_id', 'nonce' );
		foreach ( $parameters as $parameter ) {
			if ( empty( $_REQUEST[ $parameter ] ) ) {
				return;
			}
		}

		$method = 'row_action_' . $_REQUEST['row_action'];

		if ( $_REQUEST['nonce'] === wp_create_nonce( $_REQUEST[ 'row_action' ] . '::' . $_REQUEST[ 'row_id' ] ) && method_exists( $this, $method ) ) {
			$this->$method( $_REQUEST['row_id'] );
		}

		wp_redirect( remove_query_arg(
			array( 'row_id', 'row_action', 'nonce' ),
			wp_unslash( $_SERVER['REQUEST_URI'] )
		) );
		exit;
	}

	/**
	 * Default column formatting, it will escape everythig for security.
	 */
	public function column_default( $item, $column_name ) {
		echo esc_html( $item[ $column_name ] );
		$this->maybe_render_actions( $item, $column_name );
	}

	/**
	 * Renders the table list, we override the original class to render the table inside a form
	 * and to render any needed HTML (like the search box). By doing so the callee of a function can simple
	 * forget about any extra HTML.
	 */
	public function display() {
		echo '<form id="' . esc_attr( $this->_args['plural'] ) . '-filter" method="get">';
		foreach ( $_GET as $key => $value ) {
			if ( '_' === $key[0] || 'paged' === $key ) {
				continue;
			}
			echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		}
		if ( ! empty( $this->search_by ) ) {
			echo $this->search_box( $this->translate( 'Search' ), 'plugin' ); // WPCS: XSS OK
		}
		parent::display();
		echo '</form>';
	}
}
