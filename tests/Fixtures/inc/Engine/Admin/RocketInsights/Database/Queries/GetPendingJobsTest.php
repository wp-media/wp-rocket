<?php

// Offsets are in seconds relative to the test start: negative `modified_offset` = older row, positive `next_retry_offset` = not due yet, missing = default zero date.
return [
	'test_data' => [
		'notDueRowWithOldestModifiedShouldNotTakeLimitSlot' => [
			'config'   => [
				'count' => 2,
				'rows'  => [
					'not-due'      => [
						'status'            => 'pending',
						'job_id'            => 'job-not-due',
						'modified_offset'   => -3000,
						'next_retry_offset' => 300,
					],
					'due'          => [
						'status'            => 'pending',
						'job_id'            => 'job-due',
						'modified_offset'   => -2000,
						'next_retry_offset' => -100,
					],
					'default-date' => [
						'status'          => 'pending',
						'job_id'          => 'job-default-date',
						'modified_offset' => -1000,
					],
				],
			],
			'expected' => [ 'due', 'default-date' ],
		],
		'defaultDateAndDueRowsShouldBeReturnedByModifiedAsc' => [
			'config'   => [
				'count' => 10,
				'rows'  => [
					'not-due'      => [
						'status'            => 'pending',
						'job_id'            => 'job-not-due',
						'modified_offset'   => -3000,
						'next_retry_offset' => 300,
					],
					'default-date' => [
						'status'          => 'pending',
						'job_id'          => 'job-default-date',
						'modified_offset' => -2000,
					],
					'due'          => [
						'status'            => 'pending',
						'job_id'            => 'job-due',
						'modified_offset'   => -1000,
						'next_retry_offset' => -100,
					],
				],
			],
			'expected' => [ 'default-date', 'due' ],
		],
	],
];
