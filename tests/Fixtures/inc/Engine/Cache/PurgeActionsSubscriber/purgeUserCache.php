<?php

return [
	'vfs_dir'   => 'wp-content/cache/wp-rocket/',

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
					'example.org-Baz-594d03f6ae698691165999'     => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
				],
			],
		],
	],

	// Test data.
	'test_data' => [
		[
			// Username.
			'Foo',
			// Directory.
			'wp-content/cache/wp-rocket/example.org-Foo-594d03f6ae698691165999',
			// Deleted user cache files.
			[
				'wp-content/cache/wp-rocket/example.org-Foo-594d03f6ae698691165999/index.html',
				'wp-content/cache/wp-rocket/example.org-Foo-594d03f6ae698691165999/index.html_gzip',
			],
		],
		[
			// Username.
			'wpmedia',
			// Directory.
			'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999',
			// Deleted user cache files.
			[
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/index.html',
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/index.html_gzip',
			],
		],
		[
			// Username.
			'Baz',
			// Directory.
			'wp-content/cache/wp-rocket/example.org-Baz-594d03f6ae698691165999',
			// Deleted user cache files.
			[
				'wp-content/cache/wp-rocket/example.org-Baz-594d03f6ae698691165999/index.html',
				'wp-content/cache/wp-rocket/example.org-Baz-594d03f6ae698691165999/index.html_gzip',
			],
		],
	],
];
