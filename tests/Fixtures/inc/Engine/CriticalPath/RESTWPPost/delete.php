<?php


return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'critical-css' => [
					'1' => [
						'.'              => '',
						'..'             => '',
						'posts'          => [
							'.'           => '',
							'..'          => '',
							'post-1.css'  => '.p { color: red; }',
							'post-10.css' => '.p { color: red; }',
							'page-20.css' => '.p { color: red; }',
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
							'post-1.css'  => '.p { color: red; }',
							'post-3.css'  => '.p { color: red; }',
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

		'testShouldBailoutWithNoCapabilities' => [
			'config'   => [
				'cpcss_exists_before' => true,
				'current_user_can'    => false,
				'post_data'           => [
					'post_id'   => 1,
					'post_type' => 'post',
				],
				'cpcss_exists_after'  => true,
			],
			'expected' => [
				'code'    => 'rest_forbidden',
				'message' => 'Sorry, you are not allowed to do that.',
				'data'    => [ 'status' => 401 ],
			],
		],

		'testShouldBailoutIfPostDoesNotExist' => [
			'config'   => [
				'cpcss_exists_before' => false,
				'current_user_can'    => true,
				'post_data'           => [
					'post_id'     => 2,
					'post_type'   => 'post',
					'post_status' => null,
				],
				'cpcss_exists_after'  => false,
			],
			'expected' => [
				'success' => false,
				'code'    => 'post_not_exists',
				'message' => 'Requested post does not exist.',
				'data'    => [ 'status' => 400 ],
			],
		],

		'testShouldBailoutIfPostCPCSSNotExist' => [
			'config'   => [
				'cpcss_exists_before' => false,
				'current_user_can'    => true,
				'post_data'           => [
					'import_id'   => 3,
					'post_type'   => 'post',
					'post_status' => 'publish',
				],
				'cpcss_exists_after'  => false,
				'options'             => [
					'async_css_mobile' => 0,
				],
				'is_mobile'           => false,
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
				'cpcss_exists_before' => true,
				'current_user_can'    => true,
				'post_data'           => [
					'import_id'   => 1,
					'post_type'   => 'post',
					'post_status' => 'publish',
				],
				'cpcss_exists_after'  => false,
				'options'             => [
					'async_css_mobile' => 0,
				],
				'is_mobile'           => false,
			],
			'expected' => [
				'success' => true,
				'code'    => 'success',
				'message' => 'Critical CSS file deleted successfully.',
				'data'    => [ 'status' => 200 ],
			],
		],

		'testShouldReturnSuccessWhenCPCSSExist_post10' => [
			'config'   => [
				'cpcss_exists_before' => true,
				'current_user_can'    => true,
				'post_data'           => [
					'import_id'   => 10,
					'post_type'   => 'post',
					'post_status' => 'publish',
				],
				'cpcss_exists_after'  => false,
				'options'             => [
					'async_css_mobile' => 0,
				],
				'is_mobile'           => false,
			],
			'expected' => [
				'success' => true,
				'code'    => 'success',
				'message' => 'Critical CSS file deleted successfully.',
				'data'    => [ 'status' => 200 ],
			],
		],

		'testShouldReturnSuccessWhenCPCSSExist_page' => [
			'config'   => [
				'cpcss_exists_before' => true,
				'current_user_can'    => true,
				'post_data'           => [
					'import_id'   => 20,
					'post_type'   => 'page',
					'post_status' => 'publish',
				],
				'cpcss_exists_after'  => false,
				'options'             => [
					'async_css_mobile' => 0,
				],
				'is_mobile'           => false,
			],
			'expected' => [
				'success' => true,
				'code'    => 'success',
				'message' => 'Critical CSS file deleted successfully.',
				'data'    => [ 'status' => 200 ],
			],
		],
	],
];
