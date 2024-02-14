<?php

use WP_Rocket\Engine\Preload\Database\Rows\CacheRow;

$row1 = new CacheRow([
	'id' => 10,
	'is_mobile' => false,
	'url' => 'http://example1',
	'status' => 'pending',
	'next_retry_time' => '2023-11-22 02:00:00'
]);

$row2 = new CacheRow([
	'id' => 11,
	'is_mobile' => false,
	'url' => 'http://example2',
	'status' => 'pending',
	'next_retry_time' => '2023-11-22 02:00:00'

]);

$row3 = new CacheRow([
	'id' => 13,
	'is_mobile' => false,
	'url' => 'http://example3',
	'status' => 'pending',
	'next_retry_time' => '2023-11-22 02:00:00'

]);

$outdated_row = new CacheRow([
	'id' => 14,
	'is_mobile' => false,
	'url' => 'http://example3',
	'status' => 'in-progress',
	'next_retry_time' => '2023-11-22 02:00:00'

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
			'rows' => 5,
			'jobs' => [
				$row1,
				$row2,
				$row3
			],
			'transient' => 0,
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
