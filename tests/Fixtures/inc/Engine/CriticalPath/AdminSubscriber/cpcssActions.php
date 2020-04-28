<?php

return [
	'vfs_dir'   => 'public/',

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
			'plugins' => [
				'wp-rocket' => [
					'views' => [
						'metabox' => [
							'cpcss' => [
								'container.php' => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'views/metabox/cpcss/container.php' ),
								'generate.php' => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'views/metabox/cpcss/generate.php' ),
								'regenerate.php' => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'views/metabox/cpcss/regenerate.php' ),
							],
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
			'expected' => [
				'template' => 'generate',
				'disabled' => true,
			],
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
			'expected' => [
				'template' => 'regenerate',
				'disabled' => true,
			],
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
			'expected' => [
				'template' => 'generate',
				'disabled' => true,
			],
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
			'expected' => [
				'template' => 'regenerate',
				'disabled' => true,
			],
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
			'expected' => [
				'template' => 'generate',
				'disabled' => true,
			],
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
			'expected' => [
				'template' => 'regenerate',
				'disabled' => true,
			],
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
			'expected' => [
				'template' => 'generate',
				'disabled' => false,
			],
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
			'expected' => [
				'template' => 'regenerate',
				'disabled' => false,
			],
		],
	],
];
