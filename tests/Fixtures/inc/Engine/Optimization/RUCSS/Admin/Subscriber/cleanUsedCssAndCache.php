<?php

$items = [
	[
		'url'            => 'http://example.org/home',
		'css'            => 'h1{color:red;}',
		'retries'        => 3,
		'is_mobile'      => false,
	],
	[
		'url'            => 'http://example.org/home',
		'css'            => 'h1{color:red;}',
		'retries'        => 3,
		'is_mobile'      => true,
	],
];

$used_css_files = [
	'vfs://public/wp-content/cache/used-css/1/' => null,
	'vfs://public/wp-content/cache/used-css/1/'.md5( 'https://example.org/' ).'/' => null,
	'vfs://public/wp-content/cache/used-css/1/'.md5( 'https://example.org/' ).'/used.css' => null,
	'vfs://public/wp-content/cache/used-css/1/'.md5( 'https://example.org/' ).'/used-mobile.css' => null,
	'vfs://public/wp-content/cache/used-css/1/category/' => null,
	'vfs://public/wp-content/cache/used-css/1/category/level1/' => null,
	'vfs://public/wp-content/cache/used-css/1/category/level1/used.css' => null,
	'vfs://public/wp-content/cache/used-css/1/category/level1/used-mobile.css' => null,
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

				'used-css' => [
					'1' => [
						md5( 'https://example.org/' ) => [
							'used.css' => '',
							'used-mobile.css' => '',
						],
						'category' => [
							'level1' => [
								'used.css' => '',
								'used-mobile.css' => '',
							]
						]
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
				'used_css_files' => $used_css_files,
			],
		],
		'shouldNotTruncateUnusedCSSDueToSettings' => [
			'input' => [
				'items'             => $items,
				'settings'          => [
					'remove_unused_css_safelist' => [],
				],
				'old_settings'      => [
					'remove_unused_css_safelist' => [],
				],
				'cache_files'       => $cache_files,
				'used_css_files' => $used_css_files,
			],
		],
		'shouldTruncateUnusedCSS' => [
			'input' => [
				'items'             => $items,
				'settings'          => [
					'remove_unused_css_safelist' => [],
				],
				'old_settings'      => [
					'remove_unused_css_safelist' => [ 'class1' ],
				],
				'cache_files'       => $cache_files,
				'used_css_files' => $used_css_files,
				'not_completed_count' => 0,
			],
		],
		'shouldDleteCompletedUnusedCSS' => [
			'input' => [
				'items'             => $items,
				'settings'          => [
					'remove_unused_css_safelist' => [],
				],
				'old_settings'      => [
					'remove_unused_css_safelist' => [ 'class1' ],
				],
				'cache_files'       => $cache_files,
				'used_css_files' => $used_css_files,
				'not_completed_count' => 10,
			],
		],
	],
];
