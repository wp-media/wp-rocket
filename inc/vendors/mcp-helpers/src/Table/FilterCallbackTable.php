<?php
/**
 * Custom table storing "filter name -> PHP callback" mappings.
 *
 * @package MCPHelpers
 */

namespace MCPHelpers\Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data access for the filter-callback table.
 *
 * The two meaningful columns are `filter_name` and `callback`. An auto-increment
 * `id` is added as a surrogate primary key so entries can be updated and deleted
 * unambiguously (multiple callbacks may target the same filter name).
 *
 * `callback` stores a JSON callback reference — an approved-callback id plus its
 * bound args, e.g. {"id":"rocket/append-reject-uri","args":{"uri":"/x/(.*)"}}.
 * This table only persists the string; membership in the approved-callback
 * allow-list and arg validity are enforced by the callback resolver at the layer
 * above, on both write and apply.
 */
class FilterCallbackTable {

	/**
	 * Unprefixed table name.
	 */
	private const TABLE = 'mcp_filter_callbacks';

	/**
	 * DB schema version. Bump when the schema below changes.
	 */
	private const DB_VERSION = '2.0.0';

	/**
	 * Option that records the installed schema version.
	 */
	private const VERSION_OPTION = 'mcp_helpers_db_version';

	/**
	 * Fully-qualified table name, including the site prefix.
	 *
	 * @return string
	 */
	public function name(): string {
		global $wpdb;

		return $wpdb->prefix . self::TABLE;
	}

	/**
	 * Creates or upgrades the table. Idempotent; safe to call repeatedly.
	 *
	 * Intended to run from the host plugin's activation hook.
	 *
	 * @return void
	 */
	public function install(): void {
		if ( get_option( self::VERSION_OPTION ) === self::DB_VERSION ) {
			return;
		}

		global $wpdb;

		$table_name      = $this->name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			filter_name varchar(191) NOT NULL,
			callback text NOT NULL,
			PRIMARY KEY  (id),
			KEY filter_name (filter_name)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( self::VERSION_OPTION, self::DB_VERSION );
	}

	/**
	 * Returns every entry, oldest first.
	 *
	 * @return array<int, array{id:int, filter_name:string, callback:string}>
	 */
	public function all(): array {
		global $wpdb;

		// Table name is not user input; it is derived from $wpdb->prefix.
		$rows = $wpdb->get_results(
			'SELECT id, filter_name, callback FROM ' . $this->name() . ' ORDER BY id ASC',
			ARRAY_A
		);

		return array_map( [ $this, 'shape_row' ], $rows ?: [] );
	}

	/**
	 * Returns a single entry by id, or null when it does not exist.
	 *
	 * @param int $id Entry id.
	 * @return array{id:int, filter_name:string, callback:string}|null
	 */
	public function get( int $id ): ?array {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT id, filter_name, callback FROM ' . $this->name() . ' WHERE id = %d',
				$id
			),
			ARRAY_A
		);

		return $row ? $this->shape_row( $row ) : null;
	}

	/**
	 * Inserts a new entry.
	 *
	 * @param string $filter_name Filter (hook) name the callback will be attached to.
	 * @param string $callback    JSON callback reference (see class docblock).
	 * @return int|\WP_Error New entry id, or a WP_Error on invalid input / DB failure.
	 */
	public function insert( string $filter_name, string $callback ) {
		$filter_name = $this->sanitize_filter_name( $filter_name );
		$callback    = trim( $callback );

		if ( '' === $filter_name ) {
			return new \WP_Error( 'mcp_helpers_invalid_filter', __( 'The filter name must not be empty.', 'mcp-helpers' ) );
		}

		if ( '' === $callback ) {
			return new \WP_Error( 'mcp_helpers_invalid_callback', __( 'The callback reference must not be empty.', 'mcp-helpers' ) );
		}

		global $wpdb;

		$inserted = $wpdb->insert(
			$this->name(),
			[
				'filter_name' => $filter_name,
				'callback'    => $callback,
			],
			[ '%s', '%s' ]
		);

		if ( false === $inserted ) {
			return new \WP_Error( 'mcp_helpers_insert_failed', __( 'Could not save the entry.', 'mcp-helpers' ) );
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Updates an existing entry. Only the provided fields are changed.
	 *
	 * @param int                                            $id     Entry id.
	 * @param array{filter_name?:string, callback?:string} $fields Fields to change.
	 * @return true|\WP_Error True on success, WP_Error on invalid input / missing row.
	 */
	public function update( int $id, array $fields ) {
		if ( null === $this->get( $id ) ) {
			return new \WP_Error( 'mcp_helpers_not_found', __( 'No entry with that id.', 'mcp-helpers' ) );
		}

		$data    = [];
		$formats = [];

		if ( isset( $fields['filter_name'] ) ) {
			$filter_name = $this->sanitize_filter_name( $fields['filter_name'] );

			if ( '' === $filter_name ) {
				return new \WP_Error( 'mcp_helpers_invalid_filter', __( 'The filter name must not be empty.', 'mcp-helpers' ) );
			}

			$data['filter_name'] = $filter_name;
			$formats[]           = '%s';
		}

		if ( isset( $fields['callback'] ) ) {
			$callback = trim( $fields['callback'] );

			if ( '' === $callback ) {
				return new \WP_Error( 'mcp_helpers_invalid_callback', __( 'The callback reference must not be empty.', 'mcp-helpers' ) );
			}

			$data['callback'] = $callback;
			$formats[]        = '%s';
		}

		if ( empty( $data ) ) {
			return new \WP_Error( 'mcp_helpers_nothing_to_update', __( 'No fields to update were provided.', 'mcp-helpers' ) );
		}

		global $wpdb;

		$updated = $wpdb->update( $this->name(), $data, [ 'id' => $id ], $formats, [ '%d' ] );

		if ( false === $updated ) {
			return new \WP_Error( 'mcp_helpers_update_failed', __( 'Could not update the entry.', 'mcp-helpers' ) );
		}

		return true;
	}

	/**
	 * Deletes an entry by id.
	 *
	 * @param int $id Entry id.
	 * @return true|\WP_Error True on success, WP_Error when the row is missing / DB fails.
	 */
	public function delete( int $id ) {
		if ( null === $this->get( $id ) ) {
			return new \WP_Error( 'mcp_helpers_not_found', __( 'No entry with that id.', 'mcp-helpers' ) );
		}

		global $wpdb;

		$deleted = $wpdb->delete( $this->name(), [ 'id' => $id ], [ '%d' ] );

		if ( false === $deleted ) {
			return new \WP_Error( 'mcp_helpers_delete_failed', __( 'Could not delete the entry.', 'mcp-helpers' ) );
		}

		return true;
	}

	/**
	 * Normalises a raw DB row into a typed array.
	 *
	 * @param array<string, mixed> $row Raw row.
	 * @return array{id:int, filter_name:string, callback:string}
	 */
	private function shape_row( array $row ): array {
		return [
			'id'          => (int) $row['id'],
			'filter_name' => (string) $row['filter_name'],
			'callback'    => (string) $row['callback'],
		];
	}

	/**
	 * Sanitizes a filter/hook name.
	 *
	 * @param string $filter_name Raw filter name.
	 * @return string
	 */
	private function sanitize_filter_name( string $filter_name ): string {
		return trim( sanitize_text_field( $filter_name ) );
	}
}
