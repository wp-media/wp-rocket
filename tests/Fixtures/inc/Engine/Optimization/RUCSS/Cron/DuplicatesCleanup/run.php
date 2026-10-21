<?php

$start = 1000;

return [
	'test_data' => [
		'shouldReturnDoneWhenAlreadyDone'              => [
			'config'   => [
				'done'     => true,
				'locked'   => false,
				'progress' => [],
				'max_id'   => 0,
				'clock'    => [],
				'calls'    => [],
				'supports' => true,
			],
			'expected' => [
				'result' => 'done',
				'saves'  => [],
			],
		],
		'shouldReturnLockedWhenLockHeld'               => [
			'config'   => [
				'done'     => false,
				'locked'   => true,
				'progress' => [],
				'max_id'   => 0,
				'clock'    => [],
				'calls'    => [],
				'supports' => true,
			],
			'expected' => [
				'result' => 'locked',
				'saves'  => [],
			],
		],
		'shouldCompleteAndFlushGroupWhenSupported'     => [
			'config'   => [
				'done'     => false,
				'locked'   => false,
				'progress' => [],
				'max_id'   => 250,
				'clock'    => [ $start, $start ],
				'calls'    => [
					[ 0, 250, 100, 0 ],
				],
				'supports' => true,
			],
			'expected' => [
				'result' => 'done',
				'saves'  => [
					[ 'cursor' => 250, 'max' => 250 ],
				],
			],
		],
		'shouldCompleteWithoutFlushWhenGroupFlushUnsupported' => [
			'config'   => [
				'done'     => false,
				'locked'   => false,
				'progress' => [],
				'max_id'   => 250,
				'clock'    => [ $start, $start ],
				'calls'    => [
					[ 0, 250, 100, 0 ],
				],
				'supports' => false,
			],
			'expected' => [
				'result' => 'done',
				'saves'  => [
					[ 'cursor' => 250, 'max' => 250 ],
				],
			],
		],
		'shouldCompleteWithoutQueryWhenTableIsEmpty'   => [
			'config'   => [
				'done'     => false,
				'locked'   => false,
				'progress' => [],
				'max_id'   => 0,
				'clock'    => [ $start ],
				'calls'    => [],
				'supports' => true,
			],
			'expected' => [
				'result' => 'done',
				'saves'  => [],
			],
		],
		'shouldScanSameRangeAgainAfterFullBatch'       => [
			'config'   => [
				'done'     => false,
				'locked'   => false,
				'progress' => [],
				'max_id'   => 250,
				'clock'    => [ $start, $start, $start ],
				'calls'    => [
					[ 0, 250, 100, 100 ],
					[ 0, 250, 100, 3 ],
				],
				'supports' => true,
			],
			'expected' => [
				'result' => 'done',
				'saves'  => [
					[ 'cursor' => 250, 'max' => 250 ],
				],
			],
		],
		'shouldWalkRangeChunkByChunk'                  => [
			'config'   => [
				'done'     => false,
				'locked'   => false,
				'progress' => [ 'cursor' => 5000, 'max' => 12000 ],
				'max_id'   => 0,
				'clock'    => [ $start, $start, $start ],
				'calls'    => [
					[ 5000, 10000, 100, 0 ],
					[ 10000, 12000, 100, 0 ],
				],
				'supports' => true,
			],
			'expected' => [
				'result' => 'done',
				'saves'  => [
					[ 'cursor' => 10000, 'max' => 12000 ],
					[ 'cursor' => 12000, 'max' => 12000 ],
				],
			],
		],
		'shouldStopAndKeepProgressWhenBudgetIsSpent'   => [
			'config'   => [
				'done'     => false,
				'locked'   => false,
				'progress' => [ 'cursor' => 5000, 'max' => 12000 ],
				'max_id'   => 0,
				'clock'    => [ $start, $start + 21 ],
				'calls'    => [
					[ 5000, 10000, 100, 0 ],
				],
				'supports' => true,
			],
			'expected' => [
				'result' => 'partial',
				'saves'  => [
					[ 'cursor' => 10000, 'max' => 12000 ],
				],
			],
		],
		'shouldStopWithoutSavingWhenBudgetIsSpentOnFullBatch' => [
			'config'   => [
				'done'     => false,
				'locked'   => false,
				'progress' => [],
				'max_id'   => 12000,
				'clock'    => [ $start, $start + 21 ],
				'calls'    => [
					[ 0, 5000, 100, 100 ],
				],
				'supports' => true,
			],
			'expected' => [
				'result' => 'partial',
				'saves'  => [],
			],
		],
		'shouldReturnErrorWhenQueryFails'              => [
			'config'   => [
				'done'     => false,
				'locked'   => false,
				'progress' => [],
				'max_id'   => 250,
				'clock'    => [ $start ],
				'calls'    => [
					[ 0, 250, 100, false ],
				],
				'supports' => true,
			],
			'expected' => [
				'result' => 'error',
				'saves'  => [],
			],
		],
	],
];
