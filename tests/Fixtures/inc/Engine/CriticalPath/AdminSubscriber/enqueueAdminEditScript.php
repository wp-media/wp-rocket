<?php

return [
	'vfs_dir'   => 'public/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'plugins' => [
				'wp-rocket' => [
					'views' => [
						'metabox' => [
							'cpcss' => [
								'container.php' => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'views/metabox/cpcss/container.php' ),
							],
						],
					],
				],
			],
		],
	],
	'test_data' => [
		'testShouldNotEnqueueScriptDifferentPage'        => [
			'config'   => [
				'page'               => 'options-general.php',
			],
			'expected' => false,
		],
		'testShouldNotEnqueueScriptDisabledWarning'         => [
			'config'   => [
				'page'               => 'edit.php',
				'options'            => [
					'async_css' => 0,
				],
				'post'               => [
					'post_status' => 'draft',
					'post_type'   => 'post',
					'ID'          => 1,
				],
				'is_option_excluded' => true,
			],
			'expected' => false,
		],
		'testShouldNotEnqueueScriptPostNotPublishedAndOptionExcludedWarning' => [
			'config'   => [
				'page'               => 'post.php',
				'options'            => [
					'async_css' => 1,
				],
				'post'               => [
					'post_status' => 'draft',
					'post_type'   => 'post',
					'ID'          => 1,
				],
				'is_option_excluded' => true,
			],
			'expected' => false,
		],
		'testShouldNotEnqueueScriptPostNotPublishedWarning'       => [
			'config'   => [
				'page'               => 'edit.php',
				'options'            => [
					'async_css' => 1,
				],
				'post'               => [
					'post_status' => 'draft',
					'post_type'   => 'post',
					'ID'          => 1,
				],
				'is_option_excluded' => false,
			],
			'expected' => false,
		],
		'testShouldNotEnqueueScriptExcludedFromPostWarning' => [
			'config'   => [
				'page'               => 'edit.php',
				'options'            => [
					'async_css' => 1,
				],
				'post'               => [
					'post_status' => 'publish',
					'post_type'   => 'post',
					'ID'          => 1,
				],
				'is_option_excluded' => true,
			],
			'expected' => false,
		],
		'testShouldEnqueueScript'                            => [
			'config'   => [
				'page'               => 'edit.php',
				'options'            => [
					'async_css' => 1,
				],
				'post'               => [
					'post_status' => 'publish',
					'post_type'   => 'post',
					'ID'          => 1,
				],
				'is_option_excluded' => false,
			],
			'expected' => true,
		],
	],
];
