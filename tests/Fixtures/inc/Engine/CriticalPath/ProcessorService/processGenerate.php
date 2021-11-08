<?php

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'critical-css' => [
					'1' => [
						'.'            => '',
						'..'           => '',
						'critical.css' => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
					],
					'2' => [
						'critical.css' => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
					],
				],
			],
		],
	],

	// Test data.
	'test_data' => [
		'testShouldBailoutOnRequestTimeOut'       => [
			'config'   => [
				'current_user_can'   => true,
				'item_url' => 'http://example.org/?p=1',
				'item_path' => 'posts/post-1.css',
				'cpcss_exists_after' => false,
				'request_timeout'    => true,
				'mobile'             => false,
				'type'               => 'post'
			],
			'expected' => [
				'success' => false,
				'code'    => 'cpcss_generation_timeout',
				'message' => 'Critical CSS for post timeout. Please retry a little later.',
				'data'    => [
					'status' => 400,
				],
			],
		],
		'testShouldBailoutIfPostRequest400'       => [
			'config'   => [
				'current_user_can'              => true,
				'item_url' => 'http://example.org/?p=2',
				'item_path' => 'posts/post-2.css',
				'generate_post_request_data'    => [
					'code' => 400,
					'body' => '{}',
				],
				'cpcss_exists_after'            => false,
				'send_generation_request_error' => new WP_Error(
					'cpcss_generation_failed',
					'Critical CSS for http://example.org/?p=2 not generated.',
					[
						'status' => 400,
					]
				),
				'type'               => 'custom',
			],
			'expected' => [
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://example.org/?p=2 not generated.',
				'data'    => [
					'status' => 400,
				],
			],
		],
		'testShouldBailoutIfPostRequestCodeNotExpected' => [
			'config'   => [
				'current_user_can'           => true,
				'item_url' => 'http://example.org/?p=3',
				'item_path' => 'posts/post-3.css',
				'generate_post_request_data' => [
					'code' => 403,
					'body' => '{}',
				],
				'cpcss_exists_after'         => false,
				'send_generation_request_error' => new WP_Error(
					'cpcss_generation_failed',
					'Critical CSS for http://example.org/?p=3 not generated. Error: The API returned an invalid response code.',
					[
						'status' => 403,
					]
				),
				'type'               => 'custom',
			],
			'expected' => [
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://example.org/?p=3 not generated. Error: The API returned an invalid response code.',
				'data'    => [
					'status' => 403,
				],
			],
		],
		'testShouldBailoutIfPostRequestBodyEmpty' => [
			'config'   => [
				'current_user_can'           => true,
				'item_url' => 'http://example.org/?p=4',
				'item_path' => 'posts/post-4.css',
				'generate_post_request_data' => [
					'code' => 200,
					'body' => '{}',
				],
				'cpcss_exists_after'         => false,
				'send_generation_request_error' => new WP_Error(
					'cpcss_generation_failed',
					'Critical CSS for http://example.org/?p=4 not generated. Error: The API returned an empty response.',
					[
						'status' => 400,
					]
				),
				'type'               => 'custom',
			],
			'expected' => [
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://example.org/?p=4 not generated. Error: The API returned an empty response.',
				'data'    => [
					'status' => 400,
				],
			],
		],
		'testShouldBailoutIfGetRequestCode400'    => [
			'config'   => [
				'current_user_can'           => true,
				'item_url' => 'http://example.org/?p=5',
				'item_path' => 'posts/post-5.css',
				'generate_post_request_data' => [
					'code' => 200,
					'body' => '{"success":true,"data":{"id":1}}',
				],
				'generate_get_request_data'  => [
					'code' => 400,
					'body' => '{"status":400,"success":false,"message":"Error message"}',
				],
				'cpcss_exists_after'         => false,
				'cpcss_job_id'               => false,
				'get_job_details_error'      => new WP_Error(
					'cpcss_generation_failed',
					'Critical CSS for http://example.org/?p=5 not generated. Error: Error message',
					[
						'status' => 400,
					]
				),
				'type'               => 'custom',
			],
			'expected' => [
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://example.org/?p=5 not generated. Error: Error message',
				'data'    => [
					'status' => 400,
				],
			],
		],
		'testShouldBailoutIfGetRequestCode404'    => [
			'config'   => [
				'current_user_can'           => true,
				'item_url' => 'http://example.org/?p=6',
				'item_path' => 'posts/post-6.css',
				'generate_post_request_data' => [
					'code' => 200,
					'body' => '{"success":true,"data":{"id":1}}',
				],
				'generate_get_request_data'  => [
					'code' => 404,
					'body' => '{"status":404,"success":false,"message":"Job not found"}',
				],
				'cpcss_exists_after'         => false,
				'cpcss_job_id'               => false,
				'get_job_details_error'      => new WP_Error(
					'cpcss_generation_failed',
					'Critical CSS for http://example.org/?p=6 not generated. Error: Job not found',
					[
						'status' => 404,
					]
				),
				'type'               => 'custom',
			],
			'expected' => [
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://example.org/?p=6 not generated. Error: Job not found',
				'data'    => [
					'status' => 404,
				],
			],
		],
		'testShouldNotSaveCPCSSForPost'           => [
			'config'   => [
				'current_user_can'           => true,
				'item_url' => 'http://example.org/?p=7',
				'item_path' => 'posts/post-7.css',
				'generate_post_request_data' => [
					'code' => 200,
					'body' => '{"success":true,"data":{"id":1}}',
				],
				'generate_get_request_data'  => [
					'code' => 200,
					'body' => '{"status":200,"data":{"state":"complete","critical_path":"body{color:#000}"}}',
				],
				'cpcss_exists_after'         => false,
				'cpcss_job_id'               => false,
				'save_cpcss'                 => new WP_Error(
					'cpcss_generation_failed',
					'Critical CSS for http://example.org/?p=7 not generated. Error: The destination folder could not be created.',
					[
						'status' => 400,
					]
				),
				'type'               => 'custom',
			],
			'expected' => [
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://example.org/?p=7 not generated. Error: The destination folder could not be created.',
				'data'    => [
					'status' => 400,
				],
			],
		],
		'testShouldSaveCPCSSForPost'              => [
			'config'   => [
				'current_user_can'           => true,
				'item_url' => 'http://example.org/?p=8',
				'item_path' => 'posts/post-8.css',
				'generate_post_request_data' => [
					'code' => 200,
					'body' => '{"success":true,"data":{"id":1}}',
				],
				'generate_get_request_data'  => [
					'code' => 200,
					'body' => '{"status":200,"data":{"state":"complete","critical_path":"body{color:#000}"}}',
				],
				'cpcss_exists_after'         => true,
				'cpcss_job_id'               => false,
				'save_cpcss'                 => true,
				'type'               => 'post',
			],
			'expected' => [
				'code'    => 'cpcss_generation_successful',
				'message' => 'Critical CSS for post generated.'
			],
		],
		'testShouldSaveCPCSSForHome'              => [
			'config'   => [
				'current_user_can'           => true,
				'item_url' => 'http://example.org/',
				'item_path' => 'front-page.css',
				'generate_post_request_data' => [
					'code' => 200,
					'body' => '{"success":true,"data":{"id":1}}',
				],
				'generate_get_request_data'  => [
					'code' => 200,
					'body' => '{"status":200,"data":{"state":"complete","critical_path":"body{color:#000}"}}',
				],
				'cpcss_exists_after'         => true,
				'cpcss_job_id'               => false,
				'save_cpcss'                 => true,
				'type'                       => 'front-page'
			],
			'expected' => [
				'code'    => 'cpcss_generation_successful',
				'message' => 'Critical CSS for front-page generated.'
			],
		],
		'testShouldSaveCPCSSForCategory'              => [
			'config'   => [
				'current_user_can'           => true,
				'item_url' => 'http://example.org/category/categoryname',
				'item_path' => 'categoryname.css',
				'generate_post_request_data' => [
					'code' => 200,
					'body' => '{"success":true,"data":{"id":1}}',
				],
				'generate_get_request_data'  => [
					'code' => 200,
					'body' => '{"status":200,"data":{"state":"complete","critical_path":"body{color:#000}"}}',
				],
				'cpcss_exists_after'         => true,
				'cpcss_job_id'               => false,
				'save_cpcss'                 => true,
				'type'                       => 'category'
			],
			'expected' => [
				'code'    => 'cpcss_generation_successful',
				'message' => 'Critical CSS for category generated.'
			],
		],
	],
];
