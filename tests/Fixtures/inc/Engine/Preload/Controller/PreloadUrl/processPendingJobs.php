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

$outdated_row = new CacheRow([
	'id' => 14,
	'is_mobile' => false,
	'url' => 'http://example3',
	'status' => 'in-progress',
]);

return [
	'shouldPassJobsInPending' => [
		'config' => [
			'outdated_jobs' => [
				$outdated_row
			],
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
			'outdated_jobs_id' => [
				[14]
			],
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
