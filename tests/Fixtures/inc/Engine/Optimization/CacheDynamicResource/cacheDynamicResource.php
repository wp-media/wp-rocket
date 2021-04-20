<?php
/**
 * Test Data for Cache Dynamic Resource.
 */

return [
	'vfs_dir' => 'public/wp-content/',

	'test_data' => [
		// Test Data: should replace URL when dynamic file.
		[
			'style_loader_src',
			'http://example.org/wp-content/themes/twentytwenty/style.php',
			'http://example.org/wp-content/cache/busting/1/wp-content/themes/twentytwenty/style.css',
			[
				'cdn'        => 0,
				'cdn_cnames' => [],
				'cdn_zone'   => [],
			],
		],
		[
			'style_loader_src',
			'http://example.org/wp-content/plugins/hello-dolly/style.php?ver=5.3',
			'http://example.org/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/style.css',
			[
				'cdn'        => 0,
				'cdn_cnames' => [],
				'cdn_zone'   => [],
			],
		],
		[
			'script_loader_src',
			'http://example.org/wp-content/themes/twentytwenty/assets/script.php',
			'http://example.org/wp-content/cache/busting/1/wp-content/themes/twentytwenty/assets/script.js',
			[
				'cdn'        => 0,
				'cdn_cnames' => [],
				'cdn_zone'   => [],
			],
		],
		[
			'script_loader_src',
			'http://example.org/wp-content/plugins/hello-dolly/script.php?ver=5.3',
			'http://example.org/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/script.js',
			[
				'cdn'        => 0,
				'cdn_cnames' => [],
				'cdn_zone'   => [],
			],
		],

		// Test Data: should replace URL with CDN URL when dynamic file.
		[
			'style_loader_src',
			'http://example.org/wp-content/themes/twentytwenty/style.php',
			'https://123456.rocketcdn.me/wp-content/cache/busting/1/wp-content/themes/twentytwenty/style.css',
			[
				'cdn'        => 1,
				'cdn_cnames' => [
					'https://123456.rocketcdn.me',
				],
				'cdn_zone'   => [
					'all',
				],
			],
		],
		[
			'style_loader_src',
			'http://example.org/wp-content/plugins/hello-dolly/style.php?ver=5.3',
			'https://123456.rocketcdn.me/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/style.css',
			[
				'cdn'        => 1,
				'cdn_cnames' => [
					'https://123456.rocketcdn.me',
				],
				'cdn_zone'   => [
					'all',
				],
			],
		],
		[
			'script_loader_src',
			'http://example.org/wp-content/themes/twentytwenty/assets/script.php',
			'https://123456.rocketcdn.me/wp-content/cache/busting/1/wp-content/themes/twentytwenty/assets/script.js',
			[
				'cdn'        => 1,
				'cdn_cnames' => [
					'https://123456.rocketcdn.me',
				],
				'cdn_zone'   => [
					'all',
				],
			],
		],
		[
			'script_loader_src',
			'http://example.org/wp-content/plugins/hello-dolly/script.php?ver=5.3',
			'https://123456.rocketcdn.me/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/script.js',
			[
				'cdn'        => 1,
				'cdn_cnames' => [
					'https://123456.rocketcdn.me',
				],
				'cdn_zone'   => [
					'all',
				],
			],
		],
		[
			'script_loader_src',
			'http://example.org/wp-content/plugins/hello-dolly/script.php?ver=5.3',
			'https://123456.rocketcdn.me/cdnpath/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/script.js',
			[
				'cdn'        => 1,
				'cdn_cnames' => [
					'https://123456.rocketcdn.me/cdnpath',
				],
				'cdn_zone'   => [
					'all',
				],
			],
		],
		[
			'style_loader_src',
			'http://example.org/wp-content/plugins/hello-dolly/style.php?ver=5.3',
			'https://123456.rocketcdn.me/cdnpath/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/style.css',
			[
				'cdn'        => 1,
				'cdn_cnames' => [
					'https://123456.rocketcdn.me/cdnpath',
				],
				'cdn_zone'   => [
					'all',
				],
			],
		],

		// Test Data: should not replace the URL when not a dynamic file.
		[
			'style_loader_src',
			'http://example.org/wp-content/themes/storefront/style.css',
			'http://example.org/wp-content/themes/storefront/style.css',
			[
				'cdn'        => 0,
				'cdn_cnames' => [],
				'cdn_zone'   => [],
			],
		],
		[
			'script_loader_src',
			'https://example.org/wp-content/themes/storefront/script.js',
			'https://example.org/wp-content/themes/storefront/script.js',
			[
				'cdn'        => 0,
				'cdn_cnames' => [],
				'cdn_zone'   => [],
			],
		],
		[
			'style_loader_src',
			'https://example.org/wp-content/plugins/test/style.php?data=foo&ver=5.3',
			'https://example.org/wp-content/plugins/test/style.php?data=foo&ver=5.3',
			[
				'cdn'        => 0,
				'cdn_cnames' => [],
				'cdn_zone'   => [],
			],
		],
		[
			'script_loader_src',
			'https://example.org/wp-content/plugins/test/script.php?data=foo',
			'https://example.org/wp-content/plugins/test/script.php?data=foo',
			[
				'cdn'        => 0,
				'cdn_cnames' => [],
				'cdn_zone'   => [],
			],
		],
		[
			'style_loader_src',
			'http://en.example.org/wp-content/plugins/test/style.css',
			'http://en.example.org/wp-content/plugins/test/style.css',
			[
				'cdn'        => 0,
				'cdn_cnames' => [],
				'cdn_zone'   => [],
			],
		],
		[
			'script_loader_src',
			'https://example.de/wp-content/themes/storefront/assets/script.js?ver=5.3',
			'https://example.de/wp-content/themes/storefront/assets/script.js?ver=5.3',
			[
				'cdn'        => 0,
				'cdn_cnames' => [],
				'cdn_zone'   => [],
			],
		],
		[
			'style_loader_src',
			'http://123456.rocketcdn.me/wp-content/plugins/test/style.css',
			'http://123456.rocketcdn.me/wp-content/plugins/test/style.css',
			[
				'cdn'        => 1,
				'cdn_cnames' => [
					'https://123456.rocketcdn.me',
				],
				'cdn_zone'   => [
					'all',
				],
			],
		],
		[
			'style_loader_src',
			'https://fonts.googleapis.com/css?family=Oswald',
			'https://fonts.googleapis.com/css?family=Oswald',
			[
				'cdn'        => 0,
				'cdn_cnames' => [],
				'cdn_zone'   => [],
			],
		],
		[
			'style_loader_src',
			'https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,700italic,400,700,300',
			'https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,700italic,400,700,300',
			[
				'cdn'        => 0,
				'cdn_cnames' => [],
				'cdn_zone'   => [],
			],
		],
	],
];
