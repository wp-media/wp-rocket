<?php

return [
	'test_data' => [
		'shouldReturnPendingWhenNoCache' => [
			'config'   => [
				'transient_data' => false,
			],
			'expected' => 'expired',
		],

		'shouldReturnCompletedStatus' => [
			'config'   => [
				'transient_data' => [
					'status'          => 'completed',
					'recommendations' => [
						[
							'option_slug' => 'delay_js',
						],
					],
					'metadata'        => [],
					'timestamp'       => 1234567890,
				],
			],
			'expected' => 'completed',
		],

		'shouldReturnLoadingStatus' => [
			'config'   => [
				'transient_data' => [
					'status'          => 'loading',
					'recommendations' => [],
					'metadata'        => [],
					'timestamp'       => 1234567890,
				],
			],
			'expected' => 'loading',
		],

		'shouldReturnFailedStatus' => [
			'config'   => [
				'transient_data' => [
					'status'          => 'failed',
					'recommendations' => [],
					'metadata'        => [],
					'timestamp'       => 1234567890,
					'error'           => 'API Error',
				],
			],
			'expected' => 'failed',
		],

		'shouldReturnExpiredWhenStatusMissing' => [
			'config'   => [
				'transient_data' => [
					'recommendations' => [],
					'timestamp'       => 1234567890,
				],
			],
			'expected' => 'expired',
		],
	],
];
