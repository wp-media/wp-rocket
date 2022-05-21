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
		],
		'expected' => false
	],
	'failSaveShouldReturnFalse' => [
		'config' => [
			'url' => 'url',
			'query_params' => [
				'url' => 'url',
			],
			'results' => [
				$rocket_cache_row
			],
		],
		'expected' => false
	],
	'saveShouldReturnTrue' => [
		'config' => [
			'url' => 'url',
			'query_params' => [
				'url' => 'url',
			],
			'results' => [
				$rocket_cache_row
			],
		],
		'expected' => true
	]
];
