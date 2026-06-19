<?php

return [
	'testShouldReturnWPErrorWhenNoPermission' => [
		'config'   => [
			'has_permission' => false,
			'transient'      => null,
		],
		'expected' => [
			'is_error' => true,
		],
	],
	'testShouldReturnExpiredWhenNoCache' => [
		'config'   => [
			'has_permission' => true,
			'transient'      => null,
		],
		'expected' => [
			'is_error'        => false,
			'status'          => 'expired',
			'recommendations' => [],
		],
	],
	'testShouldCoercePendingStatusToLoading' => [
		'config'   => [
			'has_permission' => true,
			'transient'      => [
				'status'          => 'pending',
				'timestamp'       => 1700000000,
				'recommendations' => [],
			],
		],
		'expected' => [
			'is_error'        => false,
			'status'          => 'loading',
			'recommendations' => [],
		],
	],
	'testShouldReturnMappedRecommendationsWhenCompleted' => [
		'config'   => [
			'has_permission' => true,
			'transient'      => [
				'status'          => 'completed',
				'timestamp'       => 1700000000,
				'recommendations' => [
					[
						'option_slug' => 'lazyload',
						'title'       => 'Enable LazyLoad',
						'description' => 'Lazy load images to improve loading time.',
					],
					[
						'option_slug' => 'plugin_imagify',
						'title'       => 'Install Imagify',
						'description' => 'Optimize your images with Imagify.',
					],
				],
			],
		],
		'expected' => [
			'is_error'        => false,
			'status'          => 'completed',
			'recommendations' => [
				[
					'option_slug'    => 'lazyload',
					'title'          => 'Enable LazyLoad',
					'description'    => 'Lazy load images to improve loading time.',
					'mcp_actionable' => true,
					'mcp_ability'    => 'wp-rocket/set-option',
				],
				[
					'option_slug'    => 'plugin_imagify',
					'title'          => 'Install Imagify',
					'description'    => 'Optimize your images with Imagify.',
					'mcp_actionable' => false,
					'mcp_ability'    => null,
				],
			],
		],
	],
];
