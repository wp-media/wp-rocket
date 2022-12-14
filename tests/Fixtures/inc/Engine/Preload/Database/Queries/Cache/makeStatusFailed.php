<?php

use WP_Rocket\Engine\Preload\Database\Rows\CacheRow;

$rocket_cache_row = new CacheRow((object) [
	'id' => 10,
]);

return [
	'failSaveShouldReturnFalse' => [
		'config' => [
			'task_id' => 10,
			'results' => [
				$rocket_cache_row
			],
			'update_status' => false,
		],
		'expected' => false
	],
	'saveShouldReturnTrue' => [
		'config' => [
			'task_id' => 10,
			'results' => [
				$rocket_cache_row
			],
			'update_status' => true,
		],
		'expected' => true
	]
];
