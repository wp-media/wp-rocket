<?php

return [
	'shouldReturnStatusWhenNoBackOff'         => [
		'config'   => [
			'test_id'     => 'test-uuid-123',
			'queue_name'  => 'EU',
			'api_url'     => 'https://api.example.com/',
			'email'       => 'test@example.com',
			'key'         => 'test-key-123',
			'request_uri' => 'https://api.example.com/performance/',
			'args'        => [
				'body'    => [
					'uuid'        => 'test-uuid-123',
					'credentials' => [
						'wpr_email' => 'test@example.com',
						'wpr_key'   => 'test-key-123',
					],
				],
				'timeout' => 15,
				'method'  => 'GET',
			],
			'response'    => [
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
				'body'     => '{"status":"completed","data":{"score":90}}',
			],
			'code'        => 200,
			'body'        => '{"status":"completed","data":{"score":90}}',
		],
		'expected' => [
			'code'   => 200,
			'status' => 'completed',
			'data'   => [ 'score' => 90 ],
		],
	],
	'shouldStillSendRequestWhenBackOffActive' => [
		'config'   => [
			'test_id'        => 'test-uuid-123',
			'queue_name'     => 'EU',
			'api_url'        => 'https://api.example.com/',
			'email'          => 'test@example.com',
			'key'            => 'test-key-123',
			'request_uri'    => 'https://api.example.com/performance/',
			'args'           => [
				'body'    => [
					'uuid'        => 'test-uuid-123',
					'credentials' => [
						'wpr_email' => 'test@example.com',
						'wpr_key'   => 'test-key-123',
					],
				],
				'timeout' => 15,
				'method'  => 'GET',
			],
			'response'       => [
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
				'body'     => '{"status":"completed","data":{"score":90}}',
			],
			'code'           => 200,
			'body'           => '{"status":"completed","data":{"score":90}}',
			'blocked_offset' => 120,
		],
		'expected' => [
			'code'   => 200,
			'status' => 'completed',
			'data'   => [ 'score' => 90 ],
		],
	],
];
