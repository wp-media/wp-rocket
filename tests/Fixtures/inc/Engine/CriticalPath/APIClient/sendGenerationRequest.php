<?php

return [
	'test_data' => [
		'testShouldBailoutIfBlockExternal'     => [
			'config'   => [
				'block_external' => true,
				'item_url'     => 'http://www.example.com/?p=1',
				'response' => new WP_Error('code', 'User has blocked requests through HTTP.'),
			],
			'expected' => [
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://www.example.com/?p=1 not generated. Error: User has blocked requests through HTTP.',
				'data'    => [ 'status' => 400 ],
			],
		],
		'testShouldBailoutIfResponse400'     => [
			'config'   => [
				'item_url'     => 'http://www.example.com/?p=1',
				'response' => [
					'headers' => [],
					'body' => '{}',
					'response' => [
						'code' => 400,
					],
					'cookies' => [],
					'filename' => '',
				],
			],
			'expected' => [
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://www.example.com/?p=1 not generated.',
				'data'    => [ 'status' => 400 ],
			],
		],
		'testShouldBailoutIfResponseCodeNotExpected'     => [
			'config'   => [
				'item_url'     => 'http://www.example.com/?p=2',
				'response' => [
					'headers' => [],
					'body' => '{}',
					'response' => [
						'code' => 403,
					],
					'cookies' => [],
					'filename' => '',
				],
			],
			'expected' => [
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://www.example.com/?p=2 not generated. Error: The API returned an invalid response code.',
				'data'    => [ 'status' => 403 ],
			],
		],
		'testShouldBailoutIfResponseBodyEmpty'     => [
			'config'   => [
				'item_url'     => 'http://www.example.com/?p=3',
				'response' => [
					'headers' => [],
					'body' => '',
					'response' => [
						'code' => 200,
					],
					'cookies' => [],
					'filename' => '',
				],
			],
			'expected' => [
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://www.example.com/?p=3 not generated. Error: The API returned an empty response.',
				'data'    => [ 'status' => 400 ],
			],
		],
		'testShouldSucceed'     => [
			'config'   => [
				'item_url'     => 'http://www.example.com/?p=4',
				'response' => [
					'headers' => [],
					'body' => '{"success":true,"data":{"id":1}}',
					'response' => [
						'code' => 200,
					],
					'cookies' => [],
					'filename' => '',
				],
			],
			'expected' => [
				'success'    => true,
				'data'    => [ 'id' => 1 ],
			],
		],

		//mobile tests

		'testShouldBailoutIfResponse400Mobile'     => [
			'config'   => [
				'item_url'     => 'http://www.example.com/?p=1',
				'is_mobile' => true,
				'response' => [
					'headers' => [],
					'body' => '{}',
					'response' => [
						'code' => 400,
					],
					'cookies' => [],
					'filename' => '',
				],
			],
			'expected' => [
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://www.example.com/?p=1 on mobile not generated.',
				'data'    => [ 'status' => 400 ],
			],
		],
		'testShouldBailoutIfResponseCodeNotExpectedMobile'     => [
			'config'   => [
				'item_url'     => 'http://www.example.com/?p=2',
				'is_mobile' => true,
				'response' => [
					'headers' => [],
					'body' => '{}',
					'response' => [
						'code' => 403,
					],
					'cookies' => [],
					'filename' => '',
				],
			],
			'expected' => [
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://www.example.com/?p=2 on mobile not generated. Error: The API returned an invalid response code.',
				'data'    => [ 'status' => 403 ],
			],
		],
		'testShouldBailoutIfResponseBodyEmptyMobile'     => [
			'config'   => [
				'item_url'     => 'http://www.example.com/?p=3',
				'is_mobile' => true,
				'response' => [
					'headers' => [],
					'body' => '',
					'response' => [
						'code' => 200,
					],
					'cookies' => [],
					'filename' => '',
				],
			],
			'expected' => [
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://www.example.com/?p=3 on mobile not generated. Error: The API returned an empty response.',
				'data'    => [ 'status' => 400 ],
			],
		],
		'testShouldSucceedMobile'     => [
			'config'   => [
				'item_url'     => 'http://www.example.com/?p=4',
				'is_mobile' => true,
				'response' => [
					'headers' => [],
					'body' => '{"success":true,"data":{"id":1}}',
					'response' => [
						'code' => 200,
					],
					'cookies' => [],
					'filename' => '',
				],
			],
			'expected' => [
				'success'    => true,
				'data'    => [ 'id' => 1 ],
			],
		],
	],
];
