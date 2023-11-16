<?php
return [
	'disabledShouldBailOut' => [
		'config' => [
			'home_url' => 'http://example.org',
			'rucss_enabled' => false,
			'pending_count' => 100,
			'max_processing' => 300,
			'rows' => [
				(object) [
					'id' => 1,
					'url' => 'http://example.org',
					'is_mobile' => false,
				],
				(object) [
					'id' => 2,
					'url' => 'http://example.org/2',
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
				[
					'url' => 'http://example.org/2',
					'configs' => [
						'treeshake'      => 1,
						'rucss_safelist' => [],
						'skip_attr'      => [],
						'is_mobile'      => false,
						'is_home'        => false,
					],
					'response' => [
						'code' => 400,
						'message' => 'message',
					]
				]
			],
			'make_status_pending' => [
				[
					'id' => 1,
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
			'rucss_enabled' => true,
			'pending_count' => 100,
			'max_processing' => 300,
			'rows' => [
				(object) [
					'id' => 1,
					'url' => 'http://example.org',
					'is_mobile' => false,
				],
				(object) [
					'id' => 2,
					'url' => 'http://example.org/2',
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
				[
					'url' => 'http://example.org/2',
					'configs' => [
						'treeshake'      => 1,
						'rucss_safelist' => [],
						'skip_attr'      => [],
						'is_mobile'      => false,
						'is_home'        => false,
					],
					'response' => [
						'code' => 400,
						'message' => 'message',
					]
				]
			],
			'make_status_pending' => [
				[
					'id' => 1,
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
			'rucss_enabled' => true,
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
					'id' => 1,
					'code' => '',
					'message' => '',
				]
			]
		],
		'expected' => [
			'pending_count' => 100,
			'max_processing' => 300
		]
	],
];
