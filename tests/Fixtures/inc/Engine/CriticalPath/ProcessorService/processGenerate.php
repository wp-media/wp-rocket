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
				'post_data'          => [
					'ID'           => 21,
					'post_type'    => 'post',
					'post_status'  => 'publish',
					'post_title'   => 'CPCSS title',
					'post_content' => 'content',
				],
				'cpcss_exists_after' => false,
				'request_timeout'    => true,
			],
			'expected' => [
				'success' => false,
				'code'    => 'cpcss_generation_timeout',
				'message' => 'Critical CSS for http://example.org/?p=21 timeout. Please retry a little later.',
				'data'    => [
					'status' => 400,
				],
			],
		],
		'testShouldBailoutIfPostRequest400'       => [
			'config'   => [
				'current_user_can'              => true,
				'post_data'                     => [
					'ID'           => 21,
					'post_type'    => 'post',
					'post_status'  => 'publish',
					'post_title'   => 'CPCSS title',
					'post_content' => 'content',
				],
				'generate_post_request_data'    => [
					'code' => 400,
					'body' => '{}',
				],
				'cpcss_exists_after'            => false,
				'send_generation_request_error' => new WP_Error(
					'cpcss_generation_failed',
					'Critical CSS for http://example.org/?p=21 not generated.',
					[
						'status' => 400,
					]
				),
			],
			'expected' => [
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://example.org/?p=21 not generated.',
				'data'    => [
					'status' => 400,
				],
			],
		],
		'testShouldBailoutIfPostRequestCodeNotExpected' => [
			'config'   => [
				'current_user_can'           => true,
				'post_data'                  => [
					'ID'          => 21,
					'post_type'   => 'post',
					'post_status' => 'publish',
				],
				'generate_post_request_data' => [
					'code' => 403,
					'body' => '{}',
				],
				'cpcss_exists_after'         => false,
				'send_generation_request_error' => new WP_Error(
					'cpcss_generation_failed',
					'Critical CSS for http://example.org/?p=21 not generated. Error: The API returned an invalid response code.',
					[
						'status' => 403,
					]
				),
			],
			'expected' => [
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://example.org/?p=21 not generated. Error: The API returned an invalid response code.',
				'data'    => [
					'status' => 403,
				],
			],
		],
		'testShouldBailoutIfPostRequestBodyEmpty' => [
			'config'   => [
				'current_user_can'           => true,
				'post_data'                  => [
					'ID'          => 21,
					'post_type'   => 'post',
					'post_status' => 'publish',
				],
				'generate_post_request_data' => [
					'code' => 200,
					'body' => '{}',
				],
				'cpcss_exists_after'         => false,
				'send_generation_request_error' => new WP_Error(
					'cpcss_generation_failed',
					'Critical CSS for http://example.org/?p=21 not generated. Error: The API returned an empty response.',
					[
						'status' => 400,
					]
				),
			],
			'expected' => [
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://example.org/?p=21 not generated. Error: The API returned an empty response.',
				'data'    => [
					'status' => 400,
				],
			],
		],
		'testShouldBailoutIfGetRequestCode400'    => [
			'config'   => [
				'current_user_can'           => true,
				'post_data'                  => [
					'ID'          => 21,
					'post_type'   => 'post',
					'post_status' => 'publish',
				],
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
					'Critical CSS for http://example.org/?p=21 not generated. Error: Error message',
					[
						'status' => 400,
					]
				),
			],
			'expected' => [
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://example.org/?p=21 not generated. Error: Error message',
				'data'    => [
					'status' => 400,
				],
			],
		],
		'testShouldBailoutIfGetRequestCode404'    => [
			'config'   => [
				'current_user_can'           => true,
				'post_data'                  => [
					'ID'          => 21,
					'post_type'   => 'post',
					'post_status' => 'publish',
				],
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
					'Critical CSS for http://example.org/?p=21 not generated. Error: Job not found',
					[
						'status' => 404,
					]
				),
			],
			'expected' => [
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://example.org/?p=21 not generated. Error: Job not found',
				'data'    => [
					'status' => 404,
				],
			],
		],
		'testShouldNotSaveCPCSSForPost'           => [
			'config'   => [
				'current_user_can'           => true,
				'post_data'                  => [
					'ID'          => 21,
					'post_type'   => 'post',
					'post_status' => 'publish',
				],
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
					'Critical CSS for http://example.org/?p=21 not generated.',
					[
						'status' => 400,
					]
				),
			],
			'expected' => [
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://example.org/?p=21 not generated.',
				'data'    => [
					'status' => 400,
				],
			],
		],
		'testShouldSaveCPCSSForPost'              => [
			'config'   => [
				'current_user_can'           => true,
				'post_data'                  => [
					'ID'          => 21,
					'post_type'   => 'post',
					'post_status' => 'publish',
				],
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
			],
			'expected' => [
				'code'    => 'cpcss_generation_successful',
				'message' => 'Critical CSS for http://example.org/?p=21 generated.'
			],
		],
	],
];
