<?php

return [
	'testFailedRequestShouldReturnError' => [
		'config' => [
			'job_id' => 10,
			'queue_name' => 'EU',
			'is_home' => false,
			'api_url' => 'https://api.example.com',
			'email' => 'example@email.com',
			'key' => 'key',
			'response' => [
				'code' => 400,
				'message' => 'message',
				'body' => 'body',
			],
			'is_succeed' => false,
			'code' => 400,
			'message' => 'message',
			'request_uri' => 'https://api.example.comrucss-job',
			'args' => [
				'body' => [
					'wpr_email' => 'example@email.com',
					'wpr_key' => 'key',
					'id'          => 10,
					'force_queue' => 'EU',
					'is_home'     => false,
				],
				'timeout' => 5,
				'method' => 'GET'
			],
			'body' => '',
			'is_unauthorized' => false,
		],
		'expected' => [
			'code' => 400,
			'message' => 'message'
		]
	],
	'testUnauthorizedResponseShouldReturnResponse' => [
		'config' => [
			'job_id' => 10,
			'queue_name' => 'EU',
			'is_home' => false,
			'api_url' => 'https://api.example.com',
			'email' => 'example@email.com',
			'key' => 'key',
			'response' => [
				'code' => 200,
				'message' => 'message',
				'body' => 'body',
			],
			'is_succeed' => true,
			'code' => 200,
			'message' => 'message',
			'request_uri' => 'https://api.example.comrucss-job',
			'args' => [
				'body' => [
					'wpr_email' => 'example@email.com',
					'wpr_key' => 'key',
					'id'          => 10,
					'force_queue' => 'EU',
					'is_home'     => false,
				],
				'timeout' => 5,
				'method' => 'GET'
			],
			'body' => json_encode([
				'code' => 401,
				'returnvalue' => [
					'code'     => 401,
					'message'  => 'Unauthorized',
				]
			]),
			'is_unauthorized' => true,
			'to_merge' => [
				'code'     => 401,
				'message'  => 'Unauthorized',
			],
			'default' => [
				'code'     => 400,
				'status'   => 'failed',
				'message'  => 'Bad json',
				'contents' => [
					'success'   => false,
					'shakedCSS' => '',
				],
			],
			'merged' => [
				'code'     => 401,
				'status'   => 'failed',
				'message'  => 'Unauthorized',
				'contents' => [
					'success'   => false,
					'shakedCSS' => '',
				],
			]
		],
		'expected' => [
			'code'     => 401,
			'status'   => 'failed',
			'message'  => 'Unauthorized',
			'contents' => [
				'success'   => false,
				'shakedCSS' => '',
			],
		]
	],
	'testSucceedRequestShouldReturnBody' => [
		'config' => [
			'job_id' => 10,
			'queue_name' => 'EU',
			'is_home' => false,
			'api_url' => 'https://api.example.comrucss-job',
			'email' => 'example@email.com',
			'key' => 'key',
			'response' => [
				'code' => 200,
				'message' => 'message',
				'body' => 'body',
			],
			'is_succeed' => true,
			'code' => 200,
			'message' => 'message',
			'request_uri' => 'https://api.example.com',
			'args' => [
				'body' => [
					'wpr_email' => 'example@email.com',
					'wpr_key' => 'key',
					'id'          => 10,
					'force_queue' => 'EU',
					'is_home'     => false,
				],
				'timeout' => 5,
				'method' => 'GET'
			],
			'body' => json_encode([
				'code'     => 200,
				'returnvalue' => [
					'code'     => 200,
					'status'   => 'success',
					'message'  => 'message',
					'contents' => [
						'success'   => true,
						'shakedCSS' => 'css',
					],
				]
			]),
			'is_unauthorized' => false,
			'to_merge' => [
				'code'     => 200,
				'status'   => 'success',
				'message'  => 'message',
				'contents' => [
					'success'   => true,
					'shakedCSS' => 'css',
				],
			],
			'default' => [
				'code'     => 400,
				'status'   => 'failed',
				'message'  => 'Bad json',
				'contents' => [
					'success'   => false,
					'shakedCSS' => '',
				],
			],
			'merged' => [
				'code'     => 200,
				'status'   => 'success',
				'message'  => 'message',
				'contents' => [
					'success'   => true,
					'shakedCSS' => 'css',
				],
			]
		],
		'expected' => [
			'code'     => 200,
			'status'   => 'success',
			'message'  => 'message',
			'contents' => [
				'success'   => true,
				'shakedCSS' => 'css',
			],
		]
	]
];
