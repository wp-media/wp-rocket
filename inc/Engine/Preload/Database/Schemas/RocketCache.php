<?php

namespace WP_Rocket\Engine\Preload\Database\Schemas;

use WP_Rocket\Dependencies\Database\Schema;

class RocketCache extends Schema {

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
