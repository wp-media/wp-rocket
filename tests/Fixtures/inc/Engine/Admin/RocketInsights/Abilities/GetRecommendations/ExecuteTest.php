<?php

return [
	'testShouldReturnExpiredStatusWhenCacheMiss' => [
		'config'   => [
			'recommendations_data' => false,
		],
		'expected' => [
			'result' => [
				'status'          => 'expired',
				'recommendations' => [],
			],
		],
	],
	'testShouldPassThroughLoadingStatus' => [
		'config'   => [
			'recommendations_data' => [
				'status'          => 'loading',
				'recommendations' => [],
			],
		],
		'expected' => [
			'result' => [
				'status'          => 'loading',
				'recommendations' => [],
			],
		],
	],
	'testShouldPassThroughFailedStatus' => [
		'config'   => [
			'recommendations_data' => [
				'status'          => 'failed',
				'recommendations' => [],
			],
		],
		'expected' => [
			'result' => [
				'status'          => 'failed',
				'recommendations' => [],
			],
		],
	],
	'testShouldCoercePendingStatusToLoading' => [
		'config'   => [
			'recommendations_data' => [
				'status'          => 'pending',
				'recommendations' => [],
			],
		],
		'expected' => [
			'result' => [
				'status'          => 'loading',
				'recommendations' => [],
			],
		],
	],
	'testShouldCoerceUnknownStatusToFailed' => [
		'config'   => [
			'recommendations_data' => [
				'status'          => 'unknown_future_status',
				'recommendations' => [],
			],
		],
		'expected' => [
			'result' => [
				'status'          => 'failed',
				'recommendations' => [],
			],
		],
	],
	'testShouldSetMcpActionableAndAbilityForMixedSlugs' => [
		'config'   => [
			'recommendations_data' => [
				'status'          => 'completed',
				'recommendations' => [
					[
						'option_slug' => 'lazyload',
						'title'       => 'Enable Lazy Loading',
						'description' => 'Lazy load images to improve performance.',
					],
					[
						'option_slug' => 'plugin_rocketcdn',
						'title'       => 'Enable RocketCDN',
						'description' => 'Use RocketCDN for faster delivery.',
					],
					[
						'option_slug' => 'performance_monitoring',
						'title'       => 'Enable Performance Monitoring',
						'description' => 'Monitor your site performance.',
					],
					[
						'option_slug' => 'minify_css',
						'title'       => 'Minify CSS',
						'description' => 'Minify CSS files to reduce load time.',
					],
					[
						'option_slug' => 'plugin_imagify',
						'title'       => 'Enable Imagify',
						'description' => 'Optimize images with Imagify.',
					],
				],
			],
		],
		'expected' => [
			'result' => [
				'status'          => 'completed',
				'recommendations' => [
					[
						'option_slug'    => 'lazyload',
						'title'          => 'Enable Lazy Loading',
						'description'    => 'Lazy load images to improve performance.',
						'mcp_actionable' => true,
						'mcp_ability'    => 'wp-rocket/set-option',
					],
					[
						'option_slug'    => 'plugin_rocketcdn',
						'title'          => 'Enable RocketCDN',
						'description'    => 'Use RocketCDN for faster delivery.',
						'mcp_actionable' => false,
						'mcp_ability'    => null,
					],
					[
						'option_slug'    => 'performance_monitoring',
						'title'          => 'Enable Performance Monitoring',
						'description'    => 'Monitor your site performance.',
						'mcp_actionable' => false,
						'mcp_ability'    => null,
					],
					[
						'option_slug'    => 'minify_css',
						'title'          => 'Minify CSS',
						'description'    => 'Minify CSS files to reduce load time.',
						'mcp_actionable' => true,
						'mcp_ability'    => 'wp-rocket/set-option',
					],
					[
						'option_slug'    => 'plugin_imagify',
						'title'          => 'Enable Imagify',
						'description'    => 'Optimize images with Imagify.',
						'mcp_actionable' => false,
						'mcp_ability'    => null,
					],
				],
			],
		],
	],
	'testShouldDefaultDescriptionToEmptyStringWhenAbsent' => [
		'config'   => [
			'recommendations_data' => [
				'status'          => 'completed',
				'recommendations' => [
					[
						'option_slug' => 'defer_all_js',
						'title'       => 'Defer JavaScript',
					],
				],
			],
		],
		'expected' => [
			'result' => [
				'status'          => 'completed',
				'recommendations' => [
					[
						'option_slug'    => 'defer_all_js',
						'title'          => 'Defer JavaScript',
						'description'    => '',
						'mcp_actionable' => true,
						'mcp_ability'    => 'wp-rocket/set-option',
					],
				],
			],
		],
	],
	'testShouldPassThroughCompletedStatusWithNoRecommendations' => [
		'config'   => [
			'recommendations_data' => [
				'status'          => 'completed',
				'recommendations' => [],
			],
		],
		'expected' => [
			'result' => [
				'status'          => 'completed',
				'recommendations' => [],
			],
		],
	],
];
