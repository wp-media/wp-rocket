<?php

use WP_Rocket\Engine\Preload\Database\Rows\CacheRow;

$cache = new CacheRow((object)[]);

return [
	'shouldReturnPendingRowsWhenInProgressLessThanTotal' => [
		'config' => [
			'total' => 45,
			'in-progress' => 1,
			'results' => [
				$cache,
			]
		],
		'expected' => [
			$cache
		]
	],
	'shouldNotReturnEmptyPendingRowsWhenInProgressNotEqualsTotal' => [
		'config' => [
			'total' => 45,
			'in-progress' => 44,
			'results' => [
				$cache,
			],
		],
		'expected' => [
			$cache
		]
	],
	'shouldReturnEmptyPendingRowsWhenInProgressEqualsTotal' => [
		'config' => [
			'total' => 45,
			'in-progress' => 45,
			'results' => [
				$cache,
			],
		],
		'expected' => [
		]
	],
	'negativeCountShouldReturnEmpty' => [
		'config' => [
			'total' => -10,
			'in-progress' => 35,
			'results' => [
				$cache,
			]
		],
		'expected' => [
		]
	],
];
