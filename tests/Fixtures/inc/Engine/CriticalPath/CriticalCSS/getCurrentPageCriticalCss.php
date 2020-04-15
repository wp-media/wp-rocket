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
						'taxonomy.css'   => '.p { color: red; }',
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
		'non_multisite' => [
			'testShouldReturnDefaultCSS'                            => [
				'config'        => [
					'blog_id'       => 1,
					'expected_type' => [
						[
							'type'   => 'is_home',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_front_page',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_category',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_tag',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_tax',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_singular',
							'return' => false,
							'param'  => '',
						],
					],
					'excluded_type' => [],
				],
				'expected_file' => 'wp-content/cache/critical-css/1/front_page.css',
			],
			'testShouldReturnHomeCSS'                               => [
				'config'        => [
					'blog_id'       => 1,
					'expected_type' => [
						[
							'type'   => 'is_home',
							'return' => true,
							'param'  => '',
						],
						[
							'type'   => 'get_option',
							'return' => 'page',
							'param'  => 'show_on_front',
						],
					],
					'excluded_type' => [ 'is_front_page', 'is_category', 'is_tag', 'is_tax', 'is_singular' ],
				],
				'expected_file' => 'wp-content/cache/critical-css/1/home.css',
			],
			'testShouldReturnFrontPageCSS'                          => [
				'config'        => [
					'blog_id'       => 1,
					'expected_type' => [
						[
							'type'   => 'is_home',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_front_page',
							'return' => true,
							'param'  => '',
						],
					],
					'excluded_type' => [ 'is_category', 'is_tag', 'is_tax', 'is_singular' ],
				],
				'expected_file' => 'wp-content/cache/critical-css/1/front_page.css',
			],
			'testShouldReturnCategoryPageCSS'                       => [
				'config'        => [
					'blog_id'       => 1,
					'expected_type' => [
						[
							'type'   => 'is_home',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_front_page',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_category',
							'return' => true,
							'param'  => '',
						],
					],
					'excluded_type' => [ 'is_tag', 'is_tax', 'is_singular' ],
				],
				'expected_file' => 'wp-content/cache/critical-css/1/category.css',
			],
			'testShouldReturnTagCSS'                                => [
				'config'        => [
					'blog_id'       => 1,
					'expected_type' => [
						[
							'type'   => 'is_home',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_front_page',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_category',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_tag',
							'return' => true,
							'param'  => '',
						],
					],
					'excluded_type' => [ 'is_tax', 'is_singular' ],
				],
				'expected_file' => 'wp-content/cache/critical-css/1/post_tag.css',
			],
			'testShouldReturnTaxCSS'                                => [
				'config'        => [
					'blog_id'       => 1,
					'expected_type' => [
						[
							'type'   => 'is_home',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_front_page',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_category',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_tag',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_tax',
							'return' => true,
							'param'  => '',
						],
						[
							'type'   => 'get_queried_object',
							'return' => (object) [ 'taxonomy' => 'taxonomy' ],
							'param'  => '',
						],
					],
					'excluded_type' => [ 'is_singular' ],
				],
				'expected_file' => 'wp-content/cache/critical-css/1/taxonomy.css',
			],
			'testShouldReturnTaxDoesNotExistReturnFalseFallbackCSS' => [
				'config'        => [
					'blog_id'       => 1,
					'expected_type' => [
						[
							'type'   => 'is_home',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_front_page',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_category',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_tag',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_tax',
							'return' => true,
							'param'  => '',
						],
						[
							'type'   => 'get_queried_object',
							'return' => (object) [ 'taxonomy' => 'taxonomy_does_not_exist' ],
							'param'  => '',
						],
						[
							'type'   => 'get_rocket_option',
							'return' => '',
							'param'  => '',
						],
					],
					'excluded_type' => [ 'is_singular' ],
				],
				'expected_file'     => '',
				'expected_fallback' => false,
			],
			'testShouldReturnTaxDoesNotExistReturnFallbackCSS'      => [
				'config'        => [
					'blog_id'       => 1,
					'expected_type' => [
						[
							'type'   => 'is_home',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_front_page',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_category',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_tag',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_tax',
							'return' => true,
							'param'  => '',
						],
						[
							'type'   => 'get_queried_object',
							'return' => (object) [ 'taxonomy' => 'taxonomy_does_not_exist' ],
							'param'  => '',
						],
						[
							'type'   => 'get_rocket_option',
							'return' => '.fallback_css{ color: red; }',
							'param'  => '',
						],
					],
					'excluded_type' => [ 'is_singular' ],
				],
				'expected_file'     => '',
				'expected_fallback' => 'fallback',
			],
			'testShouldReturnSingularCSS'                           => [
				'config'        => [
					'blog_id'       => 1,
					'expected_type' => [
						[
							'type'   => 'is_home',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_front_page',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_category',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_tag',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_tax',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_singular',
							'return' => true,
							'param'  => '',
						],
						[
							'type'   => 'get_post_type',
							'return' => 'page',
							'param'  => '',
						],
						[
							'type'   => 'get_the_ID',
							'return' => '2',
							'param'  => '',
						],
					],
					'excluded_type' => [],
				],
				'expected_file'     => 'wp-content/cache/critical-css/1/page.css',
			],
			'testShouldReturnSingularCustomPostsCSS'                => [
				'config'        => [
					'blog_id'       => 1,
					'expected_type' => [
						[
							'type'   => 'is_home',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_front_page',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_category',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_tag',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_tax',
							'return' => false,
							'param'  => '',
						],
						[
							'type'   => 'is_singular',
							'return' => true,
							'param'  => '',
						],
						[
							'type'   => 'get_post_type',
							'return' => 'post',
							'param'  => '',
						],
						[
							'type'   => 'get_the_ID',
							'return' => '1',
							'param'  => '',
						],
					],
					'excluded_type' => [],
				],
				'expected_file'     => 'wp-content/cache/critical-css/1/posts/post-1.css',
			],
		],
		'multisite'     => [
			'testShouldBailoutWithNoCapabilities'        => [
			],
		],
	],
];
