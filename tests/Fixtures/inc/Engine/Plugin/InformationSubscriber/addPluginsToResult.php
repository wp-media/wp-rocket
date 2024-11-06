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
					[
						'slug' => 'backwup',
					],
					[
						'slug' => 'uk-cookie-consent',
					],
				],
			],
			'list'   => [
				'imagify',
				'seo-by-rank-math',
				'backwup',
				'uk-cookie-consent',
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
					'slug' => 'uk-cookie-consent',
				],
				[
					'slug' => 'wordpress-seo',
				],
				[
					'slug' => 'backwup',
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
					[
						'slug' => 'backwpup',
					],
					[
						'slug' => 'uk-cookie-consent',
					],
				],
			],
			'args'   => (object) [
				'browse' => 'featured',
			],
			'list' => [
				'seo-by-rank-math',
				'backwpup',
				'uk-cookie-consent',
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
				[
					'slug' => 'backwpup',
				],
				[
					'slug' => 'uk-cookie-consent',
				],
			],
		],
	],
];
