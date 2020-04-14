<?php


return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'critical-css' => [
                    'index.php' => '<?php',
					'1' => [
						'.'              => '',
						'..'             => '',
						'posts'          => [
							'.'           => '',
							'..'          => '',
						],
						'home.css'       => '.p { color: red; }',
						'front_page.css' => '.p { color: red; }',
						'category.css'   => '.p { color: red; }',
						'post_tag.css'   => '.p { color: red; }',
						'page.css'       => '.p { color: red; }',
					],
					'2' => [
						'.'              => '',
						'..'             => '',
						'posts'          => [
							'.'           => '',
							'..'          => '',
							'page-20.css' => '.p { color: red; }',
						],
						'home.css'       => '.p { color: red; }',
						'front_page.css' => '.p { color: red; }',
						'category.css'   => '.p { color: red; }',
						'post_tag.css'   => '.p { color: red; }',
						'page.css'       => '.p { color: red; }',
					],
				],
			],
		],
	],

	'test_data' => [
		'non_multisite' => [
			'testShouldBailoutIfPostDoesNotExist'          => [
				'config'   => [
					'current_user_can'    => true,
					'cpcss_exists_after'  => false,
				],
				'expected' => [
					'success' => false,
					'code'    => 'post_not_exists',
					'message' => 'Requested post does not exist',
					'data'    => [ 'status' => 400 ],
				],
            ],
            'testShouldBailoutWhenNotPublished'          => [
				'config'   => [
					'current_user_can'    => true,
					'post_data'           => [ 'ID' => 1, 'post_type' => 'post', 'post_status' => 'draft', 'post_title' => 'CPCSS title', 'post_content' => 'content' ],
					'cpcss_exists_after'  => false,
				],
				'expected' => [
					'success' => false,
					'code'    => 'post_not_published',
					'message' => 'Cannot generate CPCSS for unpublished post',
					'data'    => [
						'status' => 400,
					],
				],
			],
            'testShouldBailoutIfPostRequest400' => [
				'config'   => [
					'current_user_can'    => true,
                    'post_data'           => [ 'ID' => 1, 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'CPCSS title', 'post_content' => 'content' ],
                    'generate_post_request_data' => [ 'code' => 400, 'body' => '{}' ],
					'cpcss_exists_after'  => false,
				],
				'expected' => [
                    'success' => false,
                    'code'    => 'cpcss_generation_failed',
                    'message' => 'Critical CSS for http://example.org/?p=1 not generated.',
                    'data'    => [
                        'status' => 400,
                    ],
                ],
            ],
            'testShouldBailoutIfPostRequestNot200' => [
				'config'   => [
					'current_user_can'    => true,
                    'post_data'           => [ 'ID' => 1, 'post_type' => 'post', 'post_status' => 'publish' ],
                    'generate_post_request_data' => [ 'code' => 404, 'body' => '{}' ],
					'cpcss_exists_after'  => false,
				],
				'expected' => [
                    'success' => false,
                    'code'    => 'cpcss_generation_failed',
                    'message' => 'Critical CSS for http://example.org/?p=1 not generated. Error: The API returned an invalid response code.',
                    'data'    => [
                        'status' => 404,
                    ],
                ],
            ],
            'testShouldBailoutIfPostRequestBodyEmpty' => [
				'config'   => [
					'current_user_can'    => true,
                    'post_data'           => [ 'ID' => 1, 'post_type' => 'post', 'post_status' => 'publish' ],
                    'generate_post_request_data' => [ 'code' => 200, 'body' => '{}' ],
					'cpcss_exists_after'  => false,
				],
				'expected' => [
                    'success' => false,
                    'code'    => 'cpcss_generation_failed',
                    'message' => 'Critical CSS for http://example.org/?p=1 not generated. Error: The API returned an empty response.',
                    'data'    => [
                        'status' => 400,
                    ],
                ],
            ],
            'testShouldBailoutIfGetRequestCode400' => [
				'config'   => [
					'current_user_can'    => true,
                    'post_data'           => [ 'ID' => 1, 'post_type' => 'post', 'post_status' => 'publish' ],
                    'generate_post_request_data' => [ 'code' => 200, 'body' => '{"success":true,"data":{"id":1}}' ],
                    'generate_get_request_data'  => [ 'code' => 400, 'body' => '{"status":400,"message":"error happened"}' ],
					'cpcss_exists_after'  => false,
				],
				'expected' => [
                    'success' => false,
                    'code'    => 'cpcss_generation_failed',
                    'message' => 'Critical CSS for http://example.org/?p=1 not generated. Error: error happened',
                    'data'    => [
                        'status' => 400,
                    ],
                ],
            ],
            'testShouldSaveCPCSSForPost' => [
				'config'   => [
					'current_user_can'    => true,
                    'post_data'           => [ 'ID' => 1, 'post_type' => 'post', 'post_status' => 'publish' ],
                    'generate_post_request_data' => [ 'code' => 200, 'body' => '{"success":true,"data":{"id":1}}' ],
                    'generate_get_request_data'  => [ 'code' => 200, 'body' => '{"status":200,"data":{"state":"complete","critical_path":"body{color:#000}"}}' ],
					'cpcss_exists_after'  => true,
				],
				'expected' => [
					'success' => true,
					'code'    => 'cpcss_generation_successful',
					'message' => 'Critical CSS for http://example.org/?p=1 generated.',
					'data'    => [
						'status' => 200,
					],
				],
            ],
        ],
		'multisite'     => [
			'testShouldBailoutWithNoCapabilities'        => [
				'config'   => [
					'current_user_can'    => false,
					'post_data'           => [
						'post_id'   => 1,
						'post_type' => 'post',
					],
					'cpcss_exists_after'  => true,
					'site_id'             => 2,
				],
				'expected' => [
					'code'    => 'rest_forbidden',
					'message' => 'Sorry, you are not allowed to do that.',
					'data'    => [ 'status' => 401 ],
				],
			],
			'testShouldBailoutIfPostDoesNotExist'        => [
				'config'   => [
					'current_user_can'    => true,
					'post_data'           => [
						'post_id'   => 2,
						'post_type' => 'post',
					],
					'cpcss_exists_after'  => false,
					'site_id'             => 2,
				],
				'expected' => [
					'code'    => 'post_not_exists',
					'message' => 'Requested post does not exist',
					'data'    => [ 'status' => 400 ],
				],
			],
			'testShouldBailoutIfPostCPCSSNotExist'       => [
				'config'   => [
					'current_user_can'    => true,
					'post_data'           => [
						'import_id' => 3,
						'post_type' => 'post',
					],
					'cpcss_exists_after'  => false,
					'site_id'             => 2,
				],
				'expected' => [
					'code'    => 'cpcss_not_exists',
					'message' => 'Critical CSS file does not exist',
					'data'    => [ 'status' => 400 ],

				],
			],
			'testShouldReturnSuccessWhenCPCSSExist_post' => [
				'config'   => [
					'current_user_can'    => true,
					'post_data'           => [
						'import_id' => 1,
						'post_type' => 'post',
					],
					'cpcss_exists_after'  => false,
					'site_id'             => 2,
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