<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Database\Schemas;

use WP_Rocket\Dependencies\BerlinDB\Database\Schema;

class RocketInsights extends Schema {

	/**
	 * Array of database column objects
	 *
	 * @var array
	 */
	public $columns = [
		// ID Column.
		[
			'name'     => 'id',
			'type'     => 'bigint',
			'length'   => 20,
			'unsigned' => true,
			'extra'    => 'auto_increment',
			'primary'  => true,
			'sortable' => true,
		],
		// URL Column.
		[
			'name'       => 'url',
			'type'       => 'varchar',
			'length'     => '2000',
			'default'    => '',
			'cache_key'  => true,
			'searchable' => true,
			'sortable'   => true,
		],

		// Title Column.
		[
			'name'       => 'title',
			'type'       => 'text',
			'default'    => '',
			'cache_key'  => false,
			'searchable' => false,
			'sortable'   => false,
		],

		// IS_MOBILE column.
		[
			'name'       => 'is_mobile',
			'type'       => 'tinyint',
			'length'     => '1',
			'default'    => 0,
			'cache_key'  => true,
			'searchable' => true,
			'sortable'   => true,
		],
		// job_id Column.
		[
			'name'       => 'job_id',
			'type'       => 'varchar',
			'length'     => '255',
			'default'    => '',
			'cache_key'  => true,
			'searchable' => true,
			'sortable'   => true,
		],
		// QUEUE_NAME    column.
		[
			'name'       => 'queue_name',
			'type'       => 'varchar',
			'length'     => '255',
			'default'    => null,
			'cache_key'  => true,
			'searchable' => false,
			'sortable'   => false,
		],
		// RETRIES column.
		[
			'name'       => 'retries',
			'type'       => 'tinyint',
			'length'     => '1',
			'default'    => 1,
			'cache_key'  => false,
			'searchable' => true,
			'sortable'   => true,
		],
		// STATUS column.
		[
			'name'       => 'status',
			'type'       => 'varchar',
			'length'     => '255',
			'default'    => null,
			'cache_key'  => true,
			'searchable' => true,
			'sortable'   => false,
		],
		// DATA column.
		[
			'name'       => 'data',
			'type'       => 'text',
			'default'    => '',
			'cache_key'  => false,
			'searchable' => false,
			'sortable'   => false,
		],
		// MODIFIED column.
		[
			'name'       => 'modified',
			'type'       => 'timestamp',
			'default'    => '0000-00-00 00:00:00',
			'modified'   => true,
			'date_query' => true,
			'sortable'   => true,
		],
		// LAST_ACCESSED column.
		[
			'name'       => 'last_accessed',
			'type'       => 'timestamp',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'sortable'   => true,
		],

		// SUBMITTED_AT column.
		[
			'name'       => 'submitted_at',
			'type'       => 'timestamp',
			'default'    => null,
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
		],

		// NEXT_RETRY_TIME column.
		[
			'name'       => 'next_retry_time',
			'type'       => 'timestamp',
			'default'    => '0000-00-00 00:00:00',
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
		],

		// Score column.
		[
			'name'       => 'score',
			'type'       => 'tinyint',
			'length'     => '3',
			'default'    => 0,
			'cache_key'  => true,
			'searchable' => true,
			'sortable'   => true,
		],

		// Report URL column.
		[
			'name'       => 'report_url',
			'type'       => 'varchar',
			'length'     => '255',
			'default'    => '',
			'cache_key'  => true,
			'searchable' => true,
			'sortable'   => true,
		],

		// IS_BLURRED column.
		[
			'name'       => 'is_blurred',
			'type'       => 'tinyint',
			'length'     => '1',
			'default'    => 0,
			'cache_key'  => true,
			'searchable' => true,
			'sortable'   => true,
		],

		// error_code column.
		[
			'name'       => 'error_code',
			'type'       => 'varchar',
			'length'     => '32',
			'default'    => null,
			'cache_key'  => false,
			'searchable' => true,
			'sortable'   => true,
		],

		// error_message column.
		[
			'name'       => 'error_message',
			'type'       => 'longtext',
			'default'    => null,
			'cache_key'  => false,
			'searchable' => true,
			'sortable'   => true,
		],
	];
}
