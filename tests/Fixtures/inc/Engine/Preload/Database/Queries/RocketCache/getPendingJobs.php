<?php

use WP_Rocket\Engine\Preload\Database\Rows\RocketCacheRow;

$cache = new RocketCacheRow((object)[]);

return [
	'shouldReturnElements' => [
		'config' => [
			'total' => 10,
			'in_progress' => 9,
			'results' => [
				$cache,
			]
		],
		'expected' => [
			$cache
		]
	],
	'inProgressShouldSubtract' => [
		'config' => [
			'total' => 10,
			'in_progress' => 10,
			'results' => []
		],
		'expected' => []
	]
];
