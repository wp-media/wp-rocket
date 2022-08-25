<?php

use WP_Rocket\Engine\Preload\Database\Rows\CacheRow;

$cache = new CacheRow((object)[]);

return [
	'shouldReturnElements' => [
		'config' => [
			'total' => 1,
			'results' => [
				$cache,
			]
		],
		'expected' => [
			$cache
		]
	],
];
