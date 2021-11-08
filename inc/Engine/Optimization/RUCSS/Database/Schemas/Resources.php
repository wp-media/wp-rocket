<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Database\Schemas;

use WP_Rocket\Dependencies\Database\Schema;

/**
 * RUCSS Resources Schema.
 */
class Resources extends Schema {

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

		// TYPE column - values css / js.
		[
			'name'       => 'type',
			'type'       => 'varchar',
			'length'     => '5',
			'default'    => '',
			'cache_key'  => true,
			'searchable' => true,
			'sortable'   => true,
		],

		// CONTENT column.
		[
			'name'       => 'content',
			'type'       => 'longtext',
			'default'    => null,
			'cache_key'  => false,
			'searchable' => true,
			'sortable'   => true,
		],

		// HASH column.
		[
			'name'       => 'hash',
			'type'       => 'varchar',
			'length'     => '100',
			'default'    => '',
			'cache_key'  => true,
			'searchable' => true,
			'sortable'   => true,
		],

		// prewarmup column.
		[
			'name'       => 'prewarmup',
			'type'       => 'tinyint',
			'length'     => '1',
			'default'    => 0,
			'cache_key'  => true,
			'searchable' => true,
			'sortable'   => true,
		],

		// warmup_status column.
		[
			'name'       => 'warmup_status',
			'type'       => 'tinyint',
			'length'     => '1',
			'default'    => '0',
			'cache_key'  => true,
			'searchable' => true,
			'sortable'   => true,
		],

		// MEDIA column.
		[
			'name'       => 'media',
			'type'       => 'varchar',
			'length'     => '255',
			'default'    => 'all',
			'cache_key'  => true,
			'searchable' => true,
			'sortable'   => true,
		],

		// MODIFIED column.
		[
			'name'       => 'modified',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
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
	];
}
