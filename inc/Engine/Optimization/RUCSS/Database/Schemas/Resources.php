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

		// MODIFIED column.
		[
			'name'       => 'modified',
			'type'       => 'datetime',
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
