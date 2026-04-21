<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\RocketCDN\Database\Schemas;

use WP_Rocket\Dependencies\BerlinDB\Database\Schema;

/**
 * RocketCDN Schema class.
 */
class RocketCDN extends Schema {

	/**
	 * Array of database column objects.
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
		// Title Column.
		[
			'name'       => 'title',
			'type'       => 'varchar',
			'length'     => 255,
			'default'    => '',
			'cache_key'  => false,
			'searchable' => true,
			'sortable'   => true,
		],
		// URL Column.
		[
			'name'       => 'url',
			'type'       => 'varchar',
			'length'     => 2000,
			'default'    => '',
			'cache_key'  => true,
			'searchable' => true,
			'sortable'   => true,
		],
		// Modified Column.
		[
			'name'       => 'modified',
			'type'       => 'datetime',
			'default'    => '',
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
		],
		// Last Accessed Column.
		[
			'name'       => 'last_accessed',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true,
		],
	];
}
