<?php
return [
	'RUCSSDisabledShouldNotRun' => [
		'config' => [
			'rucss_enabled' => false,
			'rows' => [
				[
					'url' => 'http://example.org',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/2',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/3',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				]
			],
			'max_rows' => 100,
			'http' => [
				'http://localhostrucss-job' => [
					'body' => json_encode([
						'code' => 200,
						'contents' => [
							'jobId'     => 'job_id',
							'queueName' => 'queue_name',
						],
					]),
					'response' => ['code' => 200 ]
				]
			]
		],
		'expected' => [
			'rows' => [
				[
					'url' => 'http://example.org',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/2',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/3',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				]
			]
		]
	],
	'ShouldAddPending' => [
		'config' => [
			'rucss_enabled' => true,
			'rows' => [
				[
					'url' => 'http://example.org',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/2',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/3',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				]
			],
			'max_rows' => 100,
			'http' => [
				'http://localhostrucss-job' => [
					'body' => json_encode([
						'code' => 200,
						'contents' => [
							'jobId'     => 'job_id',
							'queueName' => 'queue_name',
						],
					]),
					'response' => ['code' => 200 ]
				]
			]
		],
		'expected' => [
			'rows' => [
				[
					'url' => 'http://example.org',
					'job_id' => 'job_id',
					'queue_name' => 'queue_name',
					'is_mobile' => false,
					'status'        => 'pending',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/2',
					'job_id' => 'job_id',
					'queue_name' => 'queue_name',
					'is_mobile' => false,
					'status'        => 'pending',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/3',
					'job_id' => 'job_id',
					'queue_name' => 'queue_name',
					'is_mobile' => false,
					'status'        => 'pending',
					'retries'       => 0,
				]
			]
		]
	],
    'ShouldAddRightNumberOfPending' => [
        'config' => [
			'rucss_enabled' => true,
			'rows' => [
				[
					'url' => 'http://example.org',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/2',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/3',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				]
			],
			'max_rows' => 2,
			'http' => [
				'http://localhostrucss-job' => [
					'body' => json_encode([
						'code' => 200,
						'contents' => [
							'jobId'     => 'job_id',
							'queueName' => 'queue_name',
						],
					]),
					'response' => ['code' => 200 ]
				]
			]
        ],
		'expected' => [
			'rows' => [
				[
					'url' => 'http://example.org',
					'job_id' => 'job_id',
					'queue_name' => 'queue_name',
					'is_mobile' => false,
					'status'        => 'pending',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/2',
					'job_id' => 'job_id',
					'queue_name' => 'queue_name',
					'is_mobile' => false,
					'status'        => 'pending',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/3',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				]
			]
		]
    ],
	'ShouldAddRightNumberOfPendingWithExisting' => [
		'config' => [
			'rucss_enabled' => true,
			'rows' => [
				[
					'url' => 'http://example.org',
					'job_id' => 'job_id',
					'queue_name' => 'queue_name',
					'is_mobile' => false,
					'status'        => 'pending',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/2',
					'job_id' => 'job_id',
					'queue_name' => 'queue_name',
					'is_mobile' => false,
					'status'        => 'in-progress',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/3',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/4',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				]
			],
			'max_rows' => 2,
			'http' => [
				'http://localhostrucss-job' => [
					'body' => json_encode([
						'code' => 200,
						'contents' => [
							'jobId'     => 'job_id',
							'queueName' => 'queue_name',
						],
					]),
					'response' => ['code' => 200 ]
				]
			]
		],
		'expected' => [
			'rows' => [
				[
					'url' => 'http://example.org',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'pending',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/2',
					'job_id' => 'job_id',
					'queue_name' => 'queue_name',
					'is_mobile' => false,
					'status'        => 'in-progress',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/3',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/4',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				]
			]
		]
	],
	'MaxZeroShouldLoadAll' => [
		'config' => [
			'rucss_enabled' => true,
			'rows' => [
				[
					'url' => 'http://example.org',
					'job_id' => 'job_id',
					'queue_name' => 'queue_name',
					'is_mobile' => false,
					'status'        => 'pending',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/2',
					'job_id' => 'job_id',
					'queue_name' => 'queue_name',
					'is_mobile' => false,
					'status'        => 'in-progress',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/3',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/4',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				]
			],
			'max_rows' => 0,
			'http' => [
				'http://localhostrucss-job' => [
					'body' => json_encode([
						'code' => 200,
						'contents' => [
							'jobId'     => 'job_id',
							'queueName' => 'queue_name',
						],
					]),
					'response' => ['code' => 200 ]
				]
			]
		],
		'expected' => [
			'rows' => [
				[
					'url' => 'http://example.org',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'pending',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/2',
					'job_id' => 'job_id',
					'queue_name' => 'queue_name',
					'is_mobile' => false,
					'status'        => 'in-progress',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/3',
					'job_id' => 'job_id',
					'queue_name' => 'queue_name',
					'is_mobile' => false,
					'status'        => 'pending',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/4',
					'job_id' => 'job_id',
					'queue_name' => 'queue_name',
					'is_mobile' => false,
					'status'        => 'pending',
					'retries'       => 0,
				]
			]
		]
	],
	'401ShouldFail' => [
		'config' => [
			'rucss_enabled' => true,
			'rows' => [
				[
					'url' => 'http://example.org',
					'job_id' => 'job_id',
					'queue_name' => 'queue_name',
					'is_mobile' => false,
					'status'        => 'pending',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/2',
					'job_id' => 'job_id',
					'queue_name' => 'queue_name',
					'is_mobile' => false,
					'status'        => 'in-progress',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/3',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/4',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'to-submit',
					'retries'       => 0,
				]
			],
			'max_rows' => 0,
			'http' => [
				'http://localhostrucss-job' => [
					'body' => json_encode([
						'code' => 401,
						'contents' => [
							'jobId'     => 'job_id',
							'queueName' => 'queue_name',
						],
					]),
					'response' => ['code' => 200 ]
				]
			]
		],
		'expected' => [
			'rows' => [
				[
					'url' => 'http://example.org',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'pending',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/2',
					'job_id' => 'job_id',
					'queue_name' => 'queue_name',
					'is_mobile' => false,
					'status'        => 'in-progress',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/3',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'failed',
					'retries'       => 0,
				],
				[
					'url' => 'http://example.org/4',
					'job_id' => '',
					'queue_name' => '',
					'is_mobile' => false,
					'status'        => 'failed',
					'retries'       => 0,
				]
			]
		]
	],
];
