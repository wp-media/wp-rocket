<?php

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	'test_data' => [
		'non_multisite' => [
			'testShouldBailoutIfResponse400WithOutMessage'     => [
				'config'   => [
					'job_id'        => 1,
					'item_url'      => 'http://www.example.com/?p=1',
					'response_data' => [
						'code' => 400,
						'body' => '{}',
					]
				],
				'expected' => [
					'code'    => 'cpcss_generation_failed',
					'message' => 'Critical CSS for http://www.example.com/?p=1 not generated.',
					'data'    => [ 'status' => 400 ],
				],
			],
			'testShouldBailoutIfResponse440WithOutMessage'     => [
				'config'   => [
					'job_id'        => 2,
					'item_url'      => 'http://www.example.com/?p=2',
					'response_data' => [
						'code' => 440,
						'body' => '{}',
					]
				],
				'expected' => [
					'code'    => 'cpcss_generation_failed',
					'message' => 'Critical CSS for http://www.example.com/?p=2 not generated.',
					'data'    => [ 'status' => 440 ],
				],
			],
			'testShouldBailoutIfResponse404WithOutMessage'     => [
				'config'   => [
					'job_id'        => 3,
					'item_url'      => 'http://www.example.com/?p=3',
					'response_data' => [
						'code' => 404,
						'body' => '{}',
					]
				],
				'expected' => [
					'code'    => 'cpcss_generation_failed',
					'message' => 'Critical CSS for http://www.example.com/?p=3 not generated.',
					'data'    => [ 'status' => 404 ],
				],
			],
			'testShouldBailoutIfResponse400WithMessage'     => [
				'config'   => [
					'job_id'        => 4,
					'item_url'      => 'http://www.example.com/?p=4',
					'response_data' => [
						'code' => 400,
						'body' => '{"status":400,"success":false,"message":"Error message"}',
					]
				],
				'expected' => [
					'code'    => 'cpcss_generation_failed',
					'message' => 'Critical CSS for http://www.example.com/?p=4 not generated. Error: Error message',
					'data'    => [ 'status' => 400 ],
				],
			],
			'testShouldBailoutIfResponseCodeNotExpected'     => [
				'config'   => [
					'job_id'        => 5,
					'item_url'      => 'http://www.example.com/?p=5',
					'response_data' => [
						'code' => 403,
						'body' => '{}',
					]
				],
				'expected' => [
					'code'    => 'cpcss_generation_failed',
					'message' => 'Critical CSS for http://www.example.com/?p=5 not generated. Error: The API returned an invalid response code.',
					'data'    => [ 'status' => 403 ],
				],
			],
			'testShouldBailoutIfResponseBodyEmpty'     => [
				'config'   => [
					'job_id'        => 6,
					'item_url'      => 'http://www.example.com/?p=6',
					'response_data' => [
						'code' => 200,
						'body' => '{}',
					]
				],
				'expected' => [
					'code'    => 'cpcss_generation_failed',
					'message' => 'Critical CSS for http://www.example.com/?p=6 not generated. Error: The API returned an empty response.',
					'data'    => [ 'status' => 400 ],
				],
			],
			'testShouldSucceedPending'     => [
				'config'   => [
					'job_id'        => 7,
					'item_url'      => 'http://www.example.com/?p=4',
					'response_data' => [
						'code' => 200,
						'body' => '{"status":200,"data":{"state":"pending"}}',
					]
				],
				'expected' => [
					'status'    => 200,
					'data'    => [ 'state' => 'pending' ],
				],
			],
			'testShouldSucceed'     => [
				'config'   => [
					'item_url'     => 'http://www.example.com/?p=4',
					'response_data' => [
						'code' => 200,
						'body' => '{"status":200,"data":{"state":"complete","critical_path":"body{color:#000}"}}',
					]
				],
				'expected' => [
					'status'  => 200,
					'data'    => [
						'state'         => 'complete',
						'critical_path' => 'body{color:#000}'
					],
				],
			],
		],
		'multisite'     => [
			'testShouldBailoutIfResponse400WithOutMessage'     => [
				'config'   => [
					'job_id'        => 1,
					'item_url'      => 'http://www.example.com/?p=1',
					'response_data' => [
						'code' => 400,
						'body' => '{}',
					],
					'site_id'      => 2
				],
				'expected' => [
					'code'    => 'cpcss_generation_failed',
					'message' => 'Critical CSS for http://www.example.com/?p=1 not generated.',
					'data'    => [ 'status' => 400 ],
				],
			],
			'testShouldBailoutIfResponse440WithOutMessage'     => [
				'config'   => [
					'job_id'        => 2,
					'item_url'      => 'http://www.example.com/?p=2',
					'response_data' => [
						'code' => 440,
						'body' => '{}',
					],
					'site_id'      => 2
				],
				'expected' => [
					'code'    => 'cpcss_generation_failed',
					'message' => 'Critical CSS for http://www.example.com/?p=2 not generated.',
					'data'    => [ 'status' => 440 ],
				],
			],
			'testShouldBailoutIfResponse404WithOutMessage'     => [
				'config'   => [
					'job_id'        => 3,
					'item_url'      => 'http://www.example.com/?p=3',
					'response_data' => [
						'code' => 404,
						'body' => '{}',
					],
					'site_id'      => 2
				],
				'expected' => [
					'code'    => 'cpcss_generation_failed',
					'message' => 'Critical CSS for http://www.example.com/?p=3 not generated.',
					'data'    => [ 'status' => 404 ],
				],
			],
			'testShouldBailoutIfResponse400WithMessage'     => [
				'config'   => [
					'job_id'        => 4,
					'item_url'      => 'http://www.example.com/?p=4',
					'response_data' => [
						'code' => 400,
						'body' => '{"status":400,"success":false,"message":"Error message"}',
					],
					'site_id'      => 2
				],
				'expected' => [
					'code'    => 'cpcss_generation_failed',
					'message' => 'Critical CSS for http://www.example.com/?p=4 not generated. Error: Error message',
					'data'    => [ 'status' => 400 ],
				],
			],
			'testShouldBailoutIfResponseCodeNotExpected'     => [
				'config'   => [
					'job_id'        => 5,
					'item_url'      => 'http://www.example.com/?p=5',
					'response_data' => [
						'code' => 403,
						'body' => '{}',
					],
					'site_id'      => 2
				],
				'expected' => [
					'code'    => 'cpcss_generation_failed',
					'message' => 'Critical CSS for http://www.example.com/?p=5 not generated. Error: The API returned an invalid response code.',
					'data'    => [ 'status' => 403 ],
				],
			],
			'testShouldBailoutIfResponseBodyEmpty'     => [
				'config'   => [
					'job_id'        => 6,
					'item_url'      => 'http://www.example.com/?p=6',
					'response_data' => [
						'code' => 200,
						'body' => '{}',
					],
					'site_id'       => 2
				],
				'expected' => [
					'code'    => 'cpcss_generation_failed',
					'message' => 'Critical CSS for http://www.example.com/?p=6 not generated. Error: The API returned an empty response.',
					'data'    => [ 'status' => 400 ],
				],
			],
			'testShouldSucceedPending'     => [
				'config'   => [
					'job_id'        => 7,
					'item_url'      => 'http://www.example.com/?p=4',
					'response_data' => [
						'code' => 200,
						'body' => '{"status":200,"data":{"state":"pending"}}',
					],
					'site_id'       => 2
				],
				'expected' => [
					'status'    => 200,
					'data'    => [ 'state' => 'pending' ],
				],
			],
			'testShouldSucceed'     => [
				'config'   => [
					'item_url'     => 'http://www.example.com/?p=4',
					'response_data' => [
						'code' => 200,
						'body' => '{"status":200,"data":{"state":"complete","critical_path":"body{color:#000}"}}',
					],
					'site_id'      => 2
				],
				'expected' => [
					'status'  => 200,
					'data'    => [
						'state'         => 'complete',
						'critical_path' => 'body{color:#000}'
					],
				],
			],
		],
	],
];
