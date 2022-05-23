<?php

use WP_Rocket\Engine\Preload\Database\Rows\RocketCacheRow;

$rocket_cache_row = new RocketCacheRow((object) [
	'id' => 10,
]);

return [
	'notTaskFoundShouldReturnFalse' => [
		'config' => [
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
