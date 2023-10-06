<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Database\Schemas;

use WP_Rocket\Dependencies\Database\Schema;

class AboveTheFold extends Schema {

	/**
	 * Array of database column objects
	 *
	 * @var array
	 */
	public $columns = [

		// ID column.
		[
			'name'     => 'id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'extra'    => 'auto_increment',
			'primary'  => true,
			'sortable' => true,
		],

		// URL column.
		[
			'name'       => 'url',
			'type'       => 'varchar',
			'length'     => '2000',
			'default'    => '',
			'cache_key'  => true,
			'searchable' => true,
			'sortable'   => true,
		],

		// LCP column.
		[
			'name'       => 'lcp',
			'type'       => 'longtext',
			'default'    => '',
			'cache_key'  => false,
			'searchable' => true,
			'sortable'   => true,
		],

		// Viewport column.
		[
			'name'       => 'viewport',
			'type'       => 'longtext',
			'default'    => '',
			'cache_key'  => false,
			'searchable' => true,
			'sortable'   => true,
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

		// JOB_ID column.
		[
			'name'       => 'job_id',
			'type'       => 'varchar',
			'length'     => '255',
			'default'    => null,
			'cache_key'  => true,
			'searchable' => false,
			'sortable'   => false,
		],

		// QUEUE_NAME column.
		[
			'name'       => 'queue_name',
			'type'       => 'varchar',
			'length'     => '255',
			'default'    => null,
			'cache_key'  => true,
			'searchable' => false,
			'sortable'   => false,
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

		// Submitted_at column.
		[
			'name'       => 'submitted_at',
			'type'       => 'timestamp',
			'default'    => '0000-00-00 00:00:00',
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
		],

		// MODIFIED column.
		[
			'name'       => 'modified',
			'type'       => 'timestamp',
			'default'    => '0000-00-00 00:00:00',
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
		],

		// LAST_ACCESSED column.
		[
			'name'       => 'last_accessed',
			'type'       => 'timestamp',
			'default'    => '0000-00-00 00:00:00',
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
		],
	];
}
