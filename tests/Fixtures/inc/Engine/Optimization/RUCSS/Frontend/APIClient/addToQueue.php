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
			]
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
