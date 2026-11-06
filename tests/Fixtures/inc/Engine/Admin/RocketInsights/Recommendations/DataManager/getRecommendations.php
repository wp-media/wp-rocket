<?php

return [
	'test_data' => [
		'shouldReturnCachedData' => [
			'config'   => [
				'transient_data'    => [
					'status'          => 'completed',
					'recommendations' => [
						[
							'option_slug' => 'delay_js',
							'title'       => 'Enable Delay JS',
						],
					],
					'metadata'        => [
						'language' => 'en',
					],
					'timestamp'       => 1234567890,
				],
				'should_clear_cache' => false,
			],
			'expected' => [
				'is_false' => false,
				'status'   => 'completed',
			],
		],

		'shouldReturnFalseWhenNoCache' => [
			'config'   => [
				'transient_data'     => false,
				'should_clear_cache' => false,
			],
			'expected' => [
				'is_false' => true,
			],
		],

		'shouldReturnFalseWhenInvalidStructure' => [
			'config'   => [
				'transient_data'     => [
					'recommendations' => [],
					// Missing 'status' and 'timestamp'
				],
				'should_clear_cache' => true,
			],
			'expected' => [
				'is_false' => true,
			],
		],

		'shouldReturnLoadingStatus' => [
			'config'   => [
				'transient_data'    => [
					'status'          => 'loading',
					'recommendations' => [],
					'metadata'        => [],
					'timestamp'       => 1234567890,
				],
				'should_clear_cache' => false,
			],
			'expected' => [
				'is_false' => false,
				'status'   => 'loading',
			],
		],

		'shouldReturnFailedStatus' => [
			'config'   => [
				'transient_data'    => [
					'status'          => 'failed',
					'recommendations' => [],
					'metadata'        => [],
					'timestamp'       => 1234567890,
					'error'           => 'API Error',
				],
				'should_clear_cache' => false,
			],
			'expected' => [
				'is_false' => false,
				'status'   => 'failed',
			],
		],
	],
];
