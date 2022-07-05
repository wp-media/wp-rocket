<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Database\Schemas;

use WP_Rocket\Dependencies\Database\Schema;

/**
 * RUCSS UsedCSS Schema.
 */
class UsedCSS extends Schema {

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

		// CSS column.
		[
			'name'       => 'css',
			'type'       => 'longtext',
			'default'    => null,
			'cache_key'  => false,
			'searchable' => true,
			'sortable'   => true,
		],

		// Hash column.
		[
			'name'       => 'hash',
			'type'       => 'varchar',
			'length'     => '32',
			'default'    => '',
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

		// STATUS    column.
		[
			'name'       => 'status',
			'type'       => 'varchar',
			'length'     => '255',
			'default'    => null,
			'cache_key'  => true,
			'searchable' => true,
			'sortable'   => false,
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
