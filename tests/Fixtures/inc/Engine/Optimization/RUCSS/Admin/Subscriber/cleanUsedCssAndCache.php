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
				'remove_unused_css' => false,
				'items'             => $items,
				'settings'          => [],
				'old_settings'      => [],
				'cache_files'       => $cache_files,
			],
		],
		'shouldNotTruncateUnusedCSSDueToSettings' => [
			'input' => [
				'remove_unused_css' => true,
				'items'             => $items,
				'settings'          => [
					'remove_unused_css_safelist' => [],
				],
				'old_settings'      => [
					'remove_unused_css_safelist' => [],
				],
				'cache_files'       => $cache_files,
			],
		],
		'shouldTruncateUnusedCSS' => [
			'input' => [
				'remove_unused_css' => true,
				'items'             => $items,
				'settings'          => [
					'remove_unused_css_safelist' => [],
				],
				'old_settings'      => [
					'remove_unused_css_safelist' => [ 'class1' ],
				],
				'cache_files'       => $cache_files,
			],
		],
	],
];
