<?php

return [
	'test_data' => [
		'shouldReturnFalseWhenNoUrl' => [
			'config'   => [
				'global_score_data' => [
					'status' => 'no-url',
				],
			],
			'expected' => false,
		],

		'shouldReturnTrueWhenInProgress' => [
			'config'   => [
				'global_score_data' => [
					'status' => 'in-progress',
				],
			],
			'expected' => true,
		],

		'shouldReturnTrueWhenCompleted' => [
			'config'   => [
				'global_score_data' => [
					'status' => 'completed',
				],
			],
			'expected' => true,
		],

		'shouldReturnTrueWhenFailed' => [
			'config'   => [
				'global_score_data' => [
					'status' => 'failed',
				],
			],
			'expected' => true,
		],

		'shouldReturnFalseWhenStatusMissing' => [
			'config'   => [
				'global_score_data' => [],
			],
			'expected' => false,
		],
	],
];
