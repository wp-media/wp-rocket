<?php

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	'test_data' => [
		'non_multisite' => [
			'testShouldBailoutIfPostDoesNotExist'     => [
				'config'   => [
					'current_user_can'   => true,
					'cpcss_exists_after' => false,
				],
				'expected' => [
					'success' => false,
					'code'    => 'post_not_exists',
					'message' => 'Requested post does not exist.',
					'data'    => [ 'status' => 400 ],
				],
			],
			'testShouldBailoutWhenNotPublished'       => [
				'config'   => [
					'current_user_can'   => true,
					'post_data'          => [
						'ID'           => 21,
						'post_type'    => 'post',
						'post_status'  => 'draft',
						'post_title'   => 'CPCSS title',
						'post_content' => 'content',
					],
					'cpcss_exists_after' => false,
				],
				'expected' => [
					'success' => false,
					'code'    => 'post_not_published',
					'message' => 'Cannot generate CPCSS for unpublished post.',
					'data'    => [
						'status' => 400,
					],
				],
			],
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
					'current_user_can'           => true,
					'post_data'                  => [
						'ID'           => 21,
						'post_type'    => 'post',
						'post_status'  => 'publish',
						'post_title'   => 'CPCSS title',
						'post_content' => 'content',
					],
					'generate_post_request_data' => [
						'code' => 400,
						'body' => '{}',
					],
					'cpcss_exists_after'         => false,
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
				],
				'expected' => [
					'success' => true,
					'code'    => 'cpcss_generation_successful',
					'message' => 'Critical CSS for http://example.org/?p=21 generated.',
					'data'    => [
						'status' => 200,
					],
				],
			],
		],
		'multisite'     => [
			'testShouldBailoutWithNoCapabilities'        => [
				'config'   => [
					'current_user_can'   => false,
					'post_data'          => [
						'post_id'   => 1,
						'post_type' => 'post',
					],
					'cpcss_exists_after' => true,
					'site_id'            => 2,
				],
				'expected' => [
					'code'    => 'rest_forbidden',
					'message' => 'Sorry, you are not allowed to do that.',
					'data'    => [ 'status' => 401 ],
				],
			],
			'testShouldBailoutIfPostDoesNotExist'        => [
				'config'   => [
					'current_user_can'   => true,
					'post_data'          => [
						'post_id'   => 2,
						'post_type' => 'post',
					],
					'cpcss_exists_after' => false,
					'site_id'            => 2,
				],
				'expected' => [
					'success' => false,
					'code'    => 'post_not_exists',
					'message' => 'Requested post does not exist.',
					'data'    => [ 'status' => 400 ],
				],
			],
			'testShouldBailoutIfPostCPCSSNotExist'       => [
				'config'   => [
					'current_user_can'   => true,
					'post_data'          => [
						'import_id' => 3,
						'post_type' => 'post',
					],
					'cpcss_exists_after' => false,
					'site_id'            => 2,
				],
				'expected' => [
					'success' => false,
					'code'    => 'cpcss_not_exists',
					'message' => 'Critical CSS file does not exist',
					'data'    => [ 'status' => 400 ],

				],
			],
			'testShouldReturnSuccessWhenCPCSSExist_post' => [
				'config'   => [
					'current_user_can'   => true,
					'post_data'          => [
						'import_id' => 1,
						'post_type' => 'post',
					],
					'cpcss_exists_after' => false,
					'site_id'            => 2,
				],
				'expected' => [
					'code'    => 'success',
					'message' => 'Critical CSS file deleted successfully.',
					'data'    => [ 'status' => 200 ],
				],
			],
		],
	],
];
