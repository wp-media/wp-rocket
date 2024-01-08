<?php
return [
	'disabledShouldBailOut' => [
		'config' => [
			'home_url' => 'http://example.org',
			'is_allowed' => false,
			'optimization_type' => 'rucss',
			'pending_count' => 100,
			'max_processing' => 300,
			'rows' => [
				(object) [
					'id' => 1,
					'url' => 'http://example.org',
					'is_mobile' => false,
				],
			],
			'add_to_queue' => [
				[
					'url' => 'http://example.org',
					'configs' => [
						'treeshake'      => 1,
						'rucss_safelist' => [],
						'skip_attr'      => [], 
						'is_mobile'      => false,
						'is_home'        => true,
						'optimization_list' => [
							'rucss',
							'lcp',
							'above_fold',
						],
					],
					'response' => [
						'code' => 200,
						'message' => 'message',
						'contents' => [
							'jobId' => 'jobId',
							'queueName' => 'queueName',
						]
					]
				],
			],
			'make_status_pending' => [
				[
					'url' => 'http://example.org',
					'jobId' => 'jobId',
					'queueName' => 'queueName',
					'mobile' => false,
				]
			]
		],
		'expected' => [
			'pending_count' => 100,
			'max_processing' => 300
		]
	],
    'shouldPassPending' => [
        'config' => [
			'home_url' => 'http://example.org',
			'is_allowed' => true,
			'optimization_type' => 'rucss',
			'pending_count' => 100,
			'max_processing' => 300,
			'rows' => [
				(object) [
					'id' => 1,
					'url' => 'http://example.org',
					'is_mobile' => false,
				],
			],
			'add_to_queue' => [
				[
					'url' => 'http://example.org',
					'configs' => [
						'treeshake'      => 1,
						'rucss_safelist' => [],
						'skip_attr'      => [],
						'is_mobile'      => false,
						'is_home'        => true,
						'optimization_list' => [
							'rucss',
							'lcp',
							'above_fold',
						],
					],
					'response' => [
						'code' => 200,
						'message' => 'message',
						'contents' => [
							'jobId' => 'jobId',
							'queueName' => 'queueName',
						]
					]
				],
			],
			'make_status_pending' => [
				[
					'url' => 'http://example.org',
					'jobId' => 'jobId',
					'queueName' => 'queueName',
					'mobile' => false,
				]
			],
			'make_status_failed' => []
        ],
		'expected' => [
			'pending_count' => 100,
			'max_processing' => 300
		]
    ],
	'shouldFailWhenNot200' => [
		'config' => [
			'home_url' => 'http://example.org',
			'is_allowed' => true,
			'optimization_type' => 'rucss',
			'pending_count' => 100,
			'max_processing' => 300,
			'rows' => [
				(object) [
					'id' => 1,
					'url' => 'http://example.org',
					'is_mobile' => false,
				]
			],
			'add_to_queue' => [
				[
					'url' => 'http://example.org',
					'configs' => [
						'treeshake'      => 1,
						'rucss_safelist' => [],
						'skip_attr'      => [],
						'is_mobile'      => false,
						'is_home'        => true,
						'optimization_list' => [
							'rucss',
							'lcp',
							'above_fold',
						],
					],
					'response' => [
						'code' => 401,
						'message' => 'message',
						'contents' => [
							'jobId' => 'jobId',
							'queueName' => 'queueName',
						]
					]
				]
			],
			'make_status_pending' => [

			],
			'make_status_failed' => [
				[
					'url' => 'http://example.org',
					'is_mobile' => false,
					'code' => '',
					'message' => '',
				]
			],
			'logger' => [
				'message' => 'Error when contacting the SaaS API.',
				'details' => [
					'SaaS error',
					'url'     => 'http://example.org',
					'code'    => 401,
					'message' => 'message',
				]
			]
		],
		'expected' => [
			'pending_count' => 100,
			'max_processing' => 300
		]
	],
];
