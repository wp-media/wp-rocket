<?php

$pending_args = static function ( int $number ): array {
	return [
		'number'         => $number,
		'status'         => 'pending',
		'job_id__not_in' => [
			'not_in' => '',
		],
		'date_query'     => [
			[
				'column'    => 'next_retry_time',
				'before'    => '2026-10-20 14:24:44',
				'inclusive' => false,
			],
		],
		'orderby'        => 'modified',
		'order'          => 'asc',
	];
};

$in_progress_args = [
	'count'  => true,
	'status' => 'in-progress',
];

return [
	'test_data' => [
		'inProgressReachesCountShouldReturnEmptyWithoutSecondQuery' => [
			'config'   => [
				'now'   => '2026-10-20 14:24:44',
				'count' => 10,
			],
			'expected' => [
				'queries' => [
					[
						'args'      => $in_progress_args,
						'use_cache' => true,
						'return'    => '10',
					],
				],
				'result'  => [],
			],
		],
		'inProgressBelowCountShouldQueryDueRowsWithoutCache' => [
			'config'   => [
				'now'   => '2026-10-20 14:24:44',
				'count' => 100,
			],
			'expected' => [
				'queries' => [
					[
						'args'      => $in_progress_args,
						'use_cache' => true,
						'return'    => '40',
					],
					[
						'args'      => $pending_args( 60 ),
						'use_cache' => false,
						'return'    => [ 'row-1', 'row-2' ],
					],
				],
				'result'  => [ 'row-1', 'row-2' ],
			],
		],
		'noneInProgressWithDefaultCountShouldQueryHundredRows' => [
			'config'   => [
				'now' => '2026-10-20 14:24:44',
			],
			'expected' => [
				'queries' => [
					[
						'args'      => $in_progress_args,
						'use_cache' => true,
						'return'    => '0',
					],
					[
						'args'      => $pending_args( 100 ),
						'use_cache' => false,
						'return'    => [],
					],
				],
				'result'  => [],
			],
		],
	],
];
