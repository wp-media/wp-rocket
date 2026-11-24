<?php
$scenarios = [
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

$base_config = static function ( array $override ): array {
	return array_merge(
		[
			'url'         => 'https://example.com',
			'api_url'     => 'https://api.example.com',
			'email'       => 'example@email.com',
			'key'         => 'key',
			'request_uri' => 'https://api.example.comrucss-job',
			'args'        => [
				'body'    => [
					'wpr_email' => 'example@email.com',
					'wpr_key'   => 'key',
					'url'       => 'https://example.com',
					'config'    => [
						'aa' => 'aa',
					],
				],
				'timeout' => 5,
				'method'  => 'POST',
			],
			'options'     => [
				'aa' => 'aa',
			],
		],
		$override
	);
};

$disabled_body = json_encode(
	[
		'code'    => 403,
		'error'   => 'Feature Disabled',
		'message' => 'RUCSS job submission is temporarily disabled. Please retry later.',
	]
);

$disabled_result = [
	'code'    => 503,
	'message' => 'Feature Disabled',
];

$feature_disabled = static function ( int $http_code, $retry_after, int $delay ) use ( $base_config, $disabled_body ): array {
	return $base_config(
		[
			'response'         => [
				'code'    => $http_code,
				'message' => 'message',
				'body'    => $disabled_body,
			],
			'body'             => $disabled_body,
			'feature_disabled' => true,
			'retry_after'      => $retry_after,
			'expected_delay'   => $delay,
		]
	);
};

$retry_after_cases = [
	'RetryAfter120'        => [ '120', 120 ],
	'RetryAfterMissing'    => [ '', 900 ],
	'RetryAfterZero'       => [ '0', 60 ],
	'RetryAfterTooLarge'   => [ '99999', 3600 ],
	'RetryAfterText'       => [ 'abc', 900 ],
	'RetryAfterNegative'   => [ '-5', 900 ],
	'RetryAfterRelative'   => [ 'tomorrow', 900 ],
	'RetryAfterArrayFirst' => [ [ '300', '700' ], 300 ],
];

foreach ( $retry_after_cases as $name => $case ) {
	$scenarios[ 'testFeatureDisabledHttp200' . $name . 'ShouldBackOff' ] = [
		'config'   => $feature_disabled( 200, $case[0], $case[1] ),
		'expected' => $disabled_result,
	];
}

$scenarios['testFeatureDisabledHttp503ShouldBackOff'] = [
	'config'   => $feature_disabled( 503, '120', 120 ),
	'expected' => $disabled_result,
];

$scenarios['testBackOffActiveShouldNotSendRequest'] = [
	'config'   => $base_config( [ 'blocked_offset' => 120 ] ),
	'expected' => $disabled_result,
];

$banned_host_body = json_encode(
	[
		'code'    => 403,
		'error'   => 'Banned Host',
		'message' => 'Banned.',
	]
);

$banned_host_result = [
	'code'    => 403,
	'error'   => 'Banned Host',
	'message' => 'Banned.',
];

$scenarios['testBannedHostShouldNotBeFeatureDisabled'] = [
	'config'   => $base_config(
		[
			'response'        => [
				'code'    => 200,
				'message' => 'message',
				'body'    => $banned_host_body,
			],
			'body'            => $banned_host_body,
			'is_succeed'      => true,
			'is_unauthorized' => false,
			'code'            => 200,
			'errors_count'    => 0,
			'to_merge'        => $banned_host_result,
			'default'         => [
				'code'     => 400,
				'message'  => 'No message. Defaulted in add_to_queue',
				'contents' => [
					'jobId'     => '0',
					'queueName' => '',
				],
			],
			'merged'          => $banned_host_result,
		]
	),
	'expected' => $banned_host_result,
];

return $scenarios;
