<?php

$items = [
	[
		'url'            => 'http://example.org/home',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => false,
	],
	[
		'url'            => 'http://example.org/home',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => true,
	],
];

$cache_files = [
	'vfs://public/wp-content/cache/wp-rocket/example.org/index.html'                                     => null,
	'vfs://public/wp-content/cache/wp-rocket/example.org/index.html_gzip'                                => null,
	'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/index.html'      => null,
	'vfs://public/wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/index.html_gzip' => null,
	'vfs://public/wp-content/cache/wp-rocket/example.org-Foo-594d03f6ae698691165999/index.html'          => null,
	'vfs://public/wp-content/cache/wp-rocket/example.org-Foo-594d03f6ae698691165999/index.html_gzip'     => null,
];

return [
	'vfs_dir' => 'wp-content/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org'                                => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
					'example.org-wpmedia-594d03f6ae698691165999' => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
					'example.org-Foo-594d03f6ae698691165999'     => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
				],

			],
		],
	],

	// Test data.
	'test_data' => [
		'shouldNotTruncateUnusedCSSDueToMissingSettings' => [
			'input' => [
				'items'             => $items,
				'settings'          => [],
				'old_settings'      => [],
				'cache_files'       => $cache_files,
			],
			'expected' => [
				'truncated' => false,
			],
		],
		'shouldNotTruncateUnusedCSSDueToSettings' => [
			'input' => [
				'items'             => $items,
				'settings'          => [
					'remove_unused_css' => 0,
					'cdn'               => 0,
				],
				'old_settings'      => [
					'remove_unused_css' => 1,
					'cdn'               => 1,
				],
				'cache_files'       => $cache_files,
			],
			'expected' => [
				'truncated' => false,
			],
		],
		'shouldTruncateUnusedCSSWhenCDNChanges' => [
			'input' => [
				'items'             => $items,
				'settings'          => [
					'remove_unused_css' => 1,
					'cdn'               => 0,
				],
				'old_settings'      => [
					'remove_unused_css' => 1,
					'cdn'               => 1,
				],
				'cache_files'       => $cache_files,
			],
			'expected' => [
				'truncated' => true,
				'not_completed_count' => 0,
			],
		],
		'shouldTruncateUnusedCSSWhenCNamesChanges' => [
			'input' => [
				'items'             => $items,
				'settings'          => [
					'remove_unused_css' => 1,
					'cdn'               => 1,
					'cdn_cnames'        => [
						'cdn.example.org'
					],
				],
				'old_settings'      => [
					'remove_unused_css' => 1,
					'cdn'               => 1,
					'cdn_cnames'        => [
						'cdn2.example.org'
					],
				],
				'cache_files'       => $cache_files,
			],
			'expected' => [
				'truncated' => true,
				'not_completed_count' => 0,
			],
		],

		'shouldTruncateUnusedCSSWhenZonesChanges' => [
			'input' => [
				'items'             => $items,
				'settings'          => [
					'remove_unused_css' => 1,
					'cdn'               => 1,
					'cdn_cnames'        => [
						'cdn.example.org'
					],
					'cdn_zone'          => [
						'all'
					],
				],
				'old_settings'      => [
					'remove_unused_css' => 1,
					'cdn'               => 1,
					'cdn_cnames'        => [
						'cdn.example.org'
					],
					'cdn_zone'          => [
						'css'
					],
				],
				'cache_files'       => $cache_files,
			],
			'expected' => [
				'truncated' => true,
				'not_completed_count' => 0,
			],
		],

		'shouldDeleteCompletedUnusedCSS' => [
			'input' => [
				'items'             => $items,
				'settings'          => [
					'remove_unused_css' => 1,
					'cdn'               => 0,
				],
				'old_settings'      => [
					'remove_unused_css' => 1,
					'cdn'               => 1,
				],
				'cache_files'       => $cache_files,
			],
			'expected' => [
				'truncated' => true,
				'not_completed_count' => 10,
			],
		],
	],
];
