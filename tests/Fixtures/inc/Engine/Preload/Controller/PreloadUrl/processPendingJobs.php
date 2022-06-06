<?php

use WP_Rocket\Engine\Preload\Database\Rows\CacheRow;

$row1 = new CacheRow([
	'id' => 10,
	'is_mobile' => false,
	'url' => 'http://example1',
	'status' => 'pending',
]);

$row2 = new CacheRow([
	'id' => 11,
	'is_mobile' => false,
	'url' => 'http://example2',
	'status' => 'pending',
]);

return [
	'shouldPassJobsInPending' => [
		'config' => [
			'rows' => 101,
			'jobs' => [
				$row1,
				$row2,
			]
		],
		'expected' => [
			'job_ids' => [
				[10],
				[11],
			],
			'job_urls' => [
				'http://example1',
				'http://example2',
			]
		]
	],
];
