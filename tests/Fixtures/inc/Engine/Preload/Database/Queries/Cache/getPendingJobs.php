<?php

use WP_Rocket\Engine\Preload\Database\Rows\CacheRow;

$cache = new CacheRow((object)[]);

return [
	'shouldReturnPendingRowsWhenInProgressLessThanTotal' => [
		'config' => [
			'total' => 45,
			'results' => [
				$cache,
			]
		],
		'expected' => [
			$cache
		]
	],
	'negativeCountShouldReturnEmpty' => [
		'config' => [
			'total' => -10,
			'results' => [
				$cache,
			]
		],
		'expected' => [
		]
	],
];
