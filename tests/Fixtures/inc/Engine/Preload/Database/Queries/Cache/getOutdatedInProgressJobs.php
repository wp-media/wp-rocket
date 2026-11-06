<?php

use WP_Rocket\Engine\Preload\Database\Rows\CacheRow;

$cache = new CacheRow((object)[]);

return [
	'shouldReturnInProgress' => [
		'config' => [
			'results' => [
				$cache,
			]
		],
		'expected' => [
			$cache
		]
	],
];
