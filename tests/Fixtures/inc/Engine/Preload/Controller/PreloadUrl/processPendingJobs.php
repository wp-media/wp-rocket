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

$row3 = new CacheRow([
	'id' => 13,
	'is_mobile' => false,
	'url' => 'http://example3',
	'status' => 'pending',
]);

return [
	'shouldPassJobsInPending' => [
		'config' => [
			'excluded' => [
			false,
			false,
			true,
			],
			'rows' => 101,
			'jobs' => [
				$row1,
				$row2,
				$row3
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
			],
			'job_deleted' =>
				['http://example3'],
		]
	],
];
