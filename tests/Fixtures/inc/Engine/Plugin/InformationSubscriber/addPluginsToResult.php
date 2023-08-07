<?php

$error = new WP_Error( '', '' );

return [
	'testShouldReturnDefaultWhenResultWPError' => [
		'config' => [
			'wp_error' => true,
			'result' => $error,
			'args'   => [],
			'list'   => [],
			'plugin_active' => false,
			'plugins_api'   => [],
		],
		'expected' => $error,
	],
	'testShouldReturnDefaultWhenNoBrowse' => [
		'config' => [
			'wp_error' => false,
			'result' => (object) [],
			'args'   => (object) [
				'browse' => '',
			],
			'list'   => [],
			'plugin_active' => false,
			'plugins_api'   => [],
		],
		'expected' => (object) [],
	],
	'testShouldReturnDefaultWhenNotTab' => [
		'config' => [
			'wp_error' => false,
			'result' => (object) [],
			'args'   => (object) [
				'browse' => 'favorites',
			],
			'list'   => [],
			'plugin_active' => false,
			'plugins_api'   =>[],
		],
		'expected' => (object) [],
	],
	'testShouldReturnDefaultWhenNotFirstPage' => [
		'config' => [
			'wp_error' => false,
			'result' => (object) [
				'info' => [
					'page' => 2,
				],
			],
			'args'   => (object) [
				'browse' => 'featured',
			],
			'list'   => [],
			'plugin_active' => false,
			'plugins_api'   => [],
		],
		'expected' => (object) [
			'info' => [
				'page' => 2,
			],
		],
	],
	'testShouldReturnDefaultWhenPluginActive' => [
		'config' => [
			'wp_error' => false,
			'result' => (object) [
				'info' => [
					'page' => 1,
				],
				'plugins' => [
					[
						'slug' => 'wp-rocket',
					],
				],
			],
			'args'   => (object) [
				'browse' => 'featured',
			],
			'list'   => [],
			'plugin_active' => true,
			'plugins_api'   => [],
		],
		'expected' => (object) [
			'info' => [
				'page' => 1,
			],
			'plugins' => [
				[
					'slug' => 'wp-rocket',
				],
			],
		],
	],
	'testShouldReturnUpdatedWhenPluginInArray' => [
		'config' => [
			'wp_error' => false,
			'result' => (object) [
				'info' => [
					'page' => 1,
				],
				'plugins' => [
					[
						'slug' => 'wordpress-seo',
					],
					[
						'slug' => 'imagify',
					],
					[
						'slug' => 'seo-by-rank-math',
					],
				],
			],
			'list'   => [
				'imagify',
				'seo-by-rank-math',
			],
			'args'   => (object) [
				'browse' => 'featured',
			],
			'plugin_active' => false,
			'plugins_api'   => [],
		],
		'expected' => (object) [
			'info' => [
				'page' => 1,
			],
			'plugins' => [
				[
					'slug' => 'imagify',
				],
				[
					'slug' => 'seo-by-rank-math',
				],
				[
					'slug' => 'wordpress-seo',
				],
			],
		],
	],
	'testShouldReturnUpdated' => [
		'config' => [
			'wp_error' => false,
			'result' => (object) [
				'info' => [
					'page' => 1,
				],
				'plugins' => [
					[
						'slug' => 'seo-by-rank-math',
					],
				],
			],
			'args'   => (object) [
				'browse' => 'featured',
			],
			'list' => [
				'seo-by-rank-math',
			],
			'plugin_active' => false,
			'plugins_api'   => [
				'slug' => 'imagify',
			],
		],
		'expected' => (object) [
			'info' => [
				'page' => 1,
			],
			'plugins' => [
				[
					'slug' => 'imagify',
				],
				[
					'slug' => 'seo-by-rank-math',
				],
			],
		],
	],
];
