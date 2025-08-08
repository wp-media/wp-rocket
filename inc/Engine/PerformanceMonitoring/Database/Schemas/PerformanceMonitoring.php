<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\PerformanceMonitoring\Database\Schemas;

use WP_Rocket\Dependencies\BerlinDB\Database\Schema;

class PerformanceMonitoring extends Schema {

	/**
	 * Array of database column objects
	 * 
	 * @var array
	 */
	public $columns = [
		// ID Column.
		[
			'name' => 'id',
			'type' => 'bigint',
			'length' => 20,
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
		// TEST_ID Column.
		[
			'name'       => 'test_id',
			'type' => 'varchar',
			'length' => '255',
			'default' => '',
			'cache_key' => true,
			'searchable' => true,
			'sortable' => true,
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
		// DATA Column
		[
			'name'       => 'data',
			'type'       => 'longtext',
			'default'    => '',
			'cache_key'  => true,
			'searchable' => true,
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