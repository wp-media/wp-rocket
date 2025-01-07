<?php
$old_date = 'old_date';
$current_date = 'current_date';
return [
	'shouldDeleteRowWithFailedStatus' => [
		'config' => [
			'rows' => [
				[
					'status' => 'failed',
					'url' => 'http://example.org',
					'last_accessed' => $current_date,
					'modified' => $current_date,
				],
				[
					'status' => 'completed',
					'url' => 'http://example.org/hello-world',
					'last_accessed' => $current_date,
					'modified' => $current_date,
				],
			],
		],
		'expected' => [
			'numberRowStillInDb' => 1
		],
	],
	'shouldDeleteRowWithOldEnough' => [
		'config' => [
			'rows' => [
				[
					'status' => 'in-progress',
					'url' => 'http://example.org',
					'last_accessed' => $old_date,
					'modified' => $old_date,
				]
			],
		],
		'expected' => [
			'numberRowStillInDb' => 0
		],
	],
	'shouldDeleteRowWithOldEnoughAndFailed' => [
		'config' => [
			'rows' => [
				[
					'status' => 'in-progress',
					'url' => 'http://example.org',
					'last_accessed' => $old_date,
					'modified' => $old_date,
				],
				[
					'status' => 'failed',
					'url' => 'http://example.org/hello-world',
					'last_accessed' => $current_date,
					'modified' => $current_date,
				],
				[
					'status' => 'completed',
					'url' => 'http://example.org/hello-world',
					'last_accessed' => $old_date,
					'modified' => $old_date,
				],
				[
					'status' => 'completed',
					'url' => 'http://example.org/hello-world',
					'last_accessed' => $current_date,
					'modified' => $current_date,
				],
			],
		],
		'expected' =>
			[
				'numberRowStillInDb' => 1
			],
	],
];
