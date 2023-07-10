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
			'plugins_api'   => (object) [],
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
			'plugins_api'   => (object) [],
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
			'plugins_api'   => (object) [],
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
			'plugins_api'   => (object) [],
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
					(object) [
						'slug' => 'wp-rocket',
					],
				],
			],
			'args'   => (object) [
				'browse' => 'featured',
			],
			'list'   => [],
			'plugin_active' => true,
			'plugins_api'   => (object) [],
		],
		'expected' => (object) [
			'info' => [
				'page' => 1,
			],
			'plugins' => [
				(object) [
					'slug' => 'wp-rocket',
				],
			],
		],
	],
	'testShouldReturnDefaultWhenPluginInArray' => [
		'config' => [
			'wp_error' => false,
			'result' => (object) [
				'info' => [
					'page' => 1,
				],
				'plugins' => [
					(object) [
						'slug' => 'imagify',
					],
					(object) [
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
			'plugins_api'   => (object) [],
		],
		'expected' => (object) [
			'info' => [
				'page' => 1,
			],
			'plugins' => [
				(object) [
					'slug' => 'imagify',
				],
				(object) [
					'slug' => 'seo-by-rank-math',
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
					(object) [
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
			'plugins_api'   => (object) [
				'slug' => 'imagify',
			],
		],
		'expected' => (object) [
			'info' => [
				'page' => 1,
			],
			'plugins' => [
				(object) [
					'slug' => 'seo-by-rank-math',
				],
				(object) [
					'slug' => 'imagify',
				],
			],
		],
	],
];
