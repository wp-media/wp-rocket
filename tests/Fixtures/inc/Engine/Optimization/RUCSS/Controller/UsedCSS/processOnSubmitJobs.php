<?php
return [
    'shouldPassPending' => [
        'config' => [
			'home_url' => 'http://example.org',
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

];
