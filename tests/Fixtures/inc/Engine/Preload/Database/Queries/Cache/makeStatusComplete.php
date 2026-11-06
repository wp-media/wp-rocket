<?php

use WP_Rocket\Engine\Preload\Database\Rows\CacheRow;

$rocket_cache_row = new CacheRow((object) [
	'id' => 10,
]);

return [
	'notTaskFoundShouldReturnFalse' => [
		'config' => [
			'current_time' => 123415,
			'url' => 'url',
			'query_params' => [
				'url' => 'url',
			],
			'results' => [],
			'update_status' => false,
		],
		'expected' => false
	],
	'failSaveShouldReturnFalse' => [
		'config' => [
			'current_time' => 123415,
			'url' => 'url',
			'query_params' => [
				'url' => 'url',
			],
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
			'current_time' => 123415,
			'url' => 'url',
			'query_params' => [
				'url' => 'url',
			],
			'task_id' => 10,
			'results' => [
				$rocket_cache_row
			],
			'update_status' => true,
		],
		'expected' => true
	]
];
