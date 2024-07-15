<?php
return [
	'testFailRequestShouldReturnError' => [
		'config' => [
			'url' => 'https://example.com',
			'api_url' => 'https://api.example.com',
			'email' => 'example@email.com',
			'key' => 'key',
			'response' => [
				'code' => 400,
				'message' => 'message',
				'body' => 'body',
			],
			'errors_count' => 1,
			'request_uri' => 'https://api.example.comrucss-job',
			'args' => [
				'body' => [
					'wpr_email' => 'example@email.com',
					'wpr_key' => 'key',
					'url'          => 'https://example.com',
					'config' => [
						'aa' => 'aa'
					],
				],
				'timeout' => 5,
				'method' => 'POST'
			],
			'options' => [
				'aa' => 'aa'
			],
			'is_unauthorized' => false,
			'is_succeed' => false,
			'code' => 400,
			'message' => 'message',
			'body' => '',
		],
		'expected' => [
			'code' => 400,
    		'message' => 'message'
		]
	],
	'testSucceedRequestShouldReturnBody' => [
		'config' => [
			'url' => 'https://example.com',
			'api_url' => 'https://api.example.com',
			'email' => 'example@email.com',
			'key' => 'key',
			'response' => [
				'code' => 200,
				'message' => 'message',
				'body' => 'body',
			],
			'errors_count' => 1,
			'request_uri' => 'https://api.example.comrucss-job',
			'args' => [
				'body' => [
					'wpr_email' => 'example@email.com',
					'wpr_key' => 'key',
					'url'          => 'https://example.com',
					'config' => [
						'aa' => 'aa'
					],
				],
				'timeout' => 5,
				'method' => 'POST'
			],
			'options' => [
				'aa' => 'aa'
			],
			'is_succeed' => true,
			'code' => 200,
			'message' => 'message',
			'body' => json_encode([
				'code'     => 200,
				'message'  => 'message',
				'contents' => [
					'jobId'     => 10,
					'queueName' => 'EU',
				],
			]),
			'to_merge' => [
				'code'     => 200,
				'message'  => 'message',
				'contents' => [
					'jobId'     => 10,
					'queueName' => 'EU',
				],
			],
			'merged' => [
				'code'     => 200,
				'message'  => 'message',
				'contents' => [
					'jobId'     => 10,
					'queueName' => 'EU',
				],
			],
			'default' => [
				'code'     => 400,
				'message'  => 'Bad json',
				'contents' => [
					'jobId'     => 0,
					'queueName' => '',
				],
			],
			'is_unauthorized' => false,
		],
		'expected' => [
			'code'     => 200,
			'message'  => 'message',
			'contents' => [
				'jobId'     => 10,
				'queueName' => 'EU',
			],
		]
	],
	'testUnauthorizedResponseShouldReturnResponse' => [
		'config' => [
			'url' => 'https://example.com',
			'api_url' => 'https://api.example.com',
			'email' => 'example@email.com',
			'key' => 'key',
			'response' => [
				'code' => 200,
				'message' => 'message',
				'body' => 'body',
			],
			'errors_count' => 1,
			'request_uri' => 'https://api.example.comrucss-job',
			'args' => [
				'body' => [
					'wpr_email' => 'example@email.com',
					'wpr_key' => 'key',
					'url'          => 'https://example.com',
					'config' => [
						'aa' => 'aa'
					],
				],
				'timeout' => 5,
				'method' => 'POST'
			],
			'options' => [
				'aa' => 'aa'
			],
			'is_succeed' => true,
			'code' => 200,
			'message' => 'message',
			'body' => json_encode([
				'code' => 401,
				'returnvalue' => [
					'code'     => 401,
					'message'  => 'Unauthorized',
				]
			]),
			'to_merge' => [
				'code'     => 401,
				'message'  => 'Unauthorized',
			],
			'merged' => [
				'code'     => 200,
				'message'  => 'message',
				'contents' => [
					'jobId'     => 10,
					'queueName' => 'EU',
				],
			],
			'default' => [
				'code'     => 400,
				'message'  => 'Bad json',
				'contents' => [
					'jobId'     => 0,
					'queueName' => '',
				],
			],
			'is_unauthorized' => true,
		],
		'expected' => [
			'code'     => 200,
			'message'  => 'message',
			'contents' => [
				'jobId'     => 10,
				'queueName' => 'EU',
			],
		]
	],
	'testSucceedTrailingSlashRequestShouldReturnBody' => [
		'config' => [
			'url' => 'https://example.com/test',
			'api_url' => 'https://api.example.com',
			'email' => 'example@email.com',
			'key' => 'key',
			'response' => [
				'code' => 200,
				'message' => 'message',
				'body' => 'body',
			],
			'errors_count' => 1,
			'request_uri' => 'https://api.example.comrucss-job',
			'args' => [
				'body' => [
					'wpr_email' => 'example@email.com',
					'wpr_key' => 'key',
					'url'          => 'https://example.com/test/',
					'config' => [
						'aa' => 'aa'
					],
				],
				'timeout' => 5,
				'method' => 'POST'
			],
			'options' => [
				'aa' => 'aa'
			],
			'is_succeed' => true,
			'code' => 200,
			'message' => 'message',
			'body' => json_encode([
				'code'     => 200,
				'message'  => 'message',
				'contents' => [
					'jobId'     => 10,
					'queueName' => 'EU',
				],
			]),
			'to_merge' => [
				'code'     => 200,
				'message'  => 'message',
				'contents' => [
					'jobId'     => 10,
					'queueName' => 'EU',
				],
			],
			'merged' => [
				'code'     => 200,
				'message'  => 'message',
				'contents' => [
					'jobId'     => 10,
					'queueName' => 'EU',
				],
			],
			'default' => [
				'code'     => 400,
				'message'  => 'Bad json',
				'contents' => [
					'jobId'     => 0,
					'queueName' => '',
				],
			],
			'is_unauthorized' => false,
		],
		'expected' => [
			'code'     => 200,
			'message'  => 'message',
			'contents' => [
				'jobId'     => 10,
				'queueName' => 'EU',
			],
		]
	],
];
