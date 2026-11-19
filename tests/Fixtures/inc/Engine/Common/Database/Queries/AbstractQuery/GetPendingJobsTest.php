<?php

// Offsets are in seconds relative to the test start: negative `modified_offset` = older row, positive `next_retry_offset` = not due yet, missing = default zero date.
$rucss = [
	'query'      => 'rucss_used_css_query',
	'table'      => 'wpr_rucss_used_css',
	'queue_name' => 'rucss',
];

$rows = [
	'not-due-1'    => [
		'status'            => 'pending',
		'job_id'            => 'job-not-due-1',
		'modified_offset'   => -3000,
		'next_retry_offset' => 300,
	],
	'not-due-2'    => [
		'status'            => 'pending',
		'job_id'            => 'job-not-due-2',
		'modified_offset'   => -2900,
		'next_retry_offset' => 600,
	],
	'due-1'        => [
		'status'            => 'pending',
		'job_id'            => 'job-due-1',
		'modified_offset'   => -2000,
		'next_retry_offset' => -100,
	],
	'due-2'        => [
		'status'            => 'pending',
		'job_id'            => 'job-due-2',
		'modified_offset'   => -1900,
		'next_retry_offset' => -50,
	],
	'default-date' => [
		'status'          => 'pending',
		'job_id'          => 'job-default-date',
		'modified_offset' => -1000,
	],
];

return [
	'test_data' => [
		'notDueRowsWithOlderModifiedShouldNotTakeLimitSlots' => [
			'config'   => $rucss + [
				'count' => 2,
				'rows'  => $rows,
			],
			'expected' => [ 'due-1', 'due-2' ],
		],
		'limitAboveDueRowsShouldReturnEveryDueRowByModifiedAsc' => [
			'config'   => $rucss + [
				'count' => 10,
				'rows'  => $rows,
			],
			'expected' => [ 'due-1', 'due-2', 'default-date' ],
		],
		'inProgressRowsShouldReduceTheNumberOfRows' => [
			'config'   => $rucss + [
				'count' => 3,
				'rows'  => $rows + [
					'in-progress-1' => [
						'status'          => 'in-progress',
						'job_id'          => 'job-in-progress-1',
						'modified_offset' => -4000,
					],
				],
			],
			'expected' => [ 'due-1', 'due-2' ],
		],
		'inProgressRowsReachingTheCountShouldReturnNothing' => [
			'config'   => $rucss + [
				'count' => 1,
				'rows'  => $rows + [
					'in-progress-1' => [
						'status'          => 'in-progress',
						'job_id'          => 'job-in-progress-1',
						'modified_offset' => -4000,
					],
				],
			],
			'expected' => [],
		],
		'rowsWithoutJobIdShouldBeExcluded'          => [
			'config'   => $rucss + [
				'count' => 10,
				'rows'  => $rows + [
					'no-job-id' => [
						'status'          => 'pending',
						'job_id'          => '',
						'modified_offset' => -4000,
					],
				],
			],
			'expected' => [ 'due-1', 'due-2', 'default-date' ],
		],
		'nonPendingRowsShouldBeExcluded'            => [
			'config'   => $rucss + [
				'count' => 10,
				'rows'  => [
					'completed' => [
						'status'          => 'completed',
						'job_id'          => 'job-completed',
						'modified_offset' => -4000,
					],
					'failed'    => [
						'status'          => 'failed',
						'job_id'          => 'job-failed',
						'modified_offset' => -3900,
					],
					'due-1'     => $rows['due-1'],
				],
			],
			'expected' => [ 'due-1' ],
		],
		'onlyNotDueRowsShouldReturnNothing'         => [
			'config'   => $rucss + [
				'count' => 10,
				'rows'  => [
					'not-due-1' => $rows['not-due-1'],
					'not-due-2' => $rows['not-due-2'],
				],
			],
			'expected' => [],
		],
		'rocketInsightsNotDueRowWithOldestModifiedShouldNotTakeLimitSlot' => [
			'config'   => [
				'query'      => 'ri_query',
				'table'      => 'wpr_performance_monitoring',
				'queue_name' => 'rocket_insights',
				'count'      => 2,
				'rows'       => [
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
	],
];
