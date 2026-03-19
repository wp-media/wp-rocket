<?php
return [
	'test_data' => [
		'shouldDoNothingWhenNoCache' => [
			'config'   => [
				'transient_data' => false,
			],
			'expected' => [
				'extends_transient' => false,
			],
		],

		'shouldExtendTransientWhenCacheExists' => [
			'config'   => [
				'transient_data' => [
					'status'          => 'completed',
					'timestamp'       => 1234567890,
					'recommendations' => [],
					'metadata'        => [],
					'metrics_hash'    => 'abc123',
				],
			],
			'expected' => [
				'extends_transient' => true,
			],
		],

		'shouldDoNothingWhenCacheHasInvalidStructure' => [
			'config'   => [
				'transient_data' => [
					'recommendations' => [],
					// Missing 'status' and 'timestamp'
				],
			],
			'expected' => [
				'extends_transient' => false,
			],
		],
	],
];
