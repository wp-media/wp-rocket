<?php

return [
	'vfs_dir'   => 'wp-content/cache/critical-css/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'critical-css' => [
					'1' => [
						'posts'         => [
							'post-2.css' => '.p { color: red; }',
						],
					],
				],
			],
		],
	],
	// Test Data
	'test_data' => [
		'testShouldDisplayGenerateTemplateOptionDisabledWarning' => [
			'config' => [
                'options' => [
                    'async_css' => 0,
                ],
                'post' => [
                    'post_status' => 'draft',
                    'post_type'   => 'post',
                    'ID'          => 1,
                ],
                'is_option_excluded' => true,
			],
			'expected' => 'test',
		],
		'testShouldDisplayRegenerateTemplateOptionDisabledWarning' => [
				'config' => [
				'options' => [
					'async_css' => 0,
				],
				'post' => [
					'post_status' => 'draft',
					'post_type'   => 'post',
					'ID'          => 2,
				],
				'is_option_excluded' => true,
			],
			'expected' => '',
		],
		'testShouldDisplayGenerateTemplatePostNotPublishedWarning' => [
				'config' => [
				'options' => [
					'async_css' => 1,
				],
				'post' => [
					'post_status' => 'draft',
					'post_type'   => 'post',
					'ID'          => 1,
				],
				'is_option_excluded' => true,
			],
			'expected' => '',
		],
		'testShouldDisplayGenerateTemplatePostNotPublishedWarning' => [
				'config' => [
				'options' => [
					'async_css' => 1,
				],
				'post' => [
					'post_status' => 'draft',
					'post_type'   => 'post',
					'ID'          => 2,
				],
				'is_option_excluded' => true,
			],
			'expected' => '',
		],
		'testShouldDisplayGenerateTemplateOptionExcludedFromPostWarning' => [
				'config' => [
				'options' => [
					'async_css' => 1,
				],
				'post' => [
					'post_status' => 'publish',
					'post_type'   => 'post',
					'ID'          => 1,
				],
				'is_option_excluded' => true,
			],
			'expected' => '',
		],
		'testShouldDisplayGenerateTemplateOptionExcludedFromPostWarning' => [
				'config' => [
				'options' => [
					'async_css' => 1,
				],
				'post' => [
					'post_status' => 'publish',
					'post_type'   => 'post',
					'ID'          => 2,
				],
				'is_option_excluded' => true,
			],
			'expected' => '',
		],
		'testShouldDisplayGenerateTemplateNoWarning' => [
				'config' => [
				'options' => [
					'async_css' => 1,
				],
				'post' => [
					'post_status' => 'publish',
					'post_type'   => 'post',
					'ID'          => 1,
				],
				'is_option_excluded' => false,
			],
			'expected' => '',
		],
		'testShouldDisplayRegenerateTemplateNoWarning' => [
				'config' => [
				'options' => [
					'async_css' => 1,
				],
				'post' => [
					'post_status' => 'publish',
					'post_type'   => 'post',
					'ID'          => 2,
				],
				'is_option_excluded' => false,
			],
			'expected' => '',
		],
	],
];
