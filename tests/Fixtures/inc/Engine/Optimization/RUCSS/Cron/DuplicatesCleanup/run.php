<?php

$start = 1000;

return [
	'test_data' => [
		'shouldReturnDoneWhenAlreadyDone' => [
			'config'   => [
				'done'     => true,
				'locked'   => false,
				'index'    => true,
				'clock'    => [  ],
				'calls'    => [  ],
				'supports' => true,
			],
			'expected' => 'done',
		],
		'shouldReturnLockedWhenLockHeld' => [
			'config'   => [
				'done'     => false,
				'locked'   => true,
				'index'    => true,
				'clock'    => [  ],
				'calls'    => [  ],
				'supports' => true,
			],
			'expected' => 'locked',
		],
		'shouldReturnNotReadyWithoutWorkWhenTheKeyIsMissing' => [
			'config'   => [
				'done'     => false,
				'locked'   => false,
				'index'    => false,
				'clock'    => [  ],
				'calls'    => [  ],
				'supports' => true,
			],
			'expected' => 'not_ready',
		],
		'shouldCompleteAndFlushGroupWhenNothingToDelete' => [
			'config'   => [
				'done'     => false,
				'locked'   => false,
				'index'    => true,
				'clock'    => [ $start, $start ],
				'calls'    => [ 0 ],
				'supports' => true,
			],
			'expected' => 'done',
		],
		'shouldCompleteWithoutFlushWhenGroupFlushUnsupported' => [
			'config'   => [
				'done'     => false,
				'locked'   => false,
				'index'    => true,
				'clock'    => [ $start, $start ],
				'calls'    => [ 0 ],
				'supports' => false,
			],
			'expected' => 'done',
		],
		'shouldCompleteAfterSeveralPasses' => [
			'config'   => [
				'done'     => false,
				'locked'   => false,
				'index'    => true,
				'clock'    => [ $start, $start, $start, $start ],
				'calls'    => [ 100, 100, 3, 0 ],
				'supports' => true,
			],
			'expected' => 'done',
		],
		'shouldReturnPartialWhenBudgetIsSpent' => [
			'config'   => [
				'done'     => false,
				'locked'   => false,
				'index'    => true,
				'clock'    => [ $start, $start + 21 ],
				'calls'    => [ 100 ],
				'supports' => true,
			],
			'expected' => 'partial',
		],
		'shouldReturnErrorWithoutFlaggingDoneOnDatabaseError' => [
			'config'   => [
				'done'     => false,
				'locked'   => false,
				'index'    => true,
				'clock'    => [ $start, $start ],
				'calls'    => [ 100, false ],
				'supports' => true,
			],
			'expected' => 'error',
		],
	],
];
