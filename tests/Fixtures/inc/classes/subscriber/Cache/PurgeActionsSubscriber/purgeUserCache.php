<?php

return [
	'vfs_dir'   => 'cache/wp-rocket/',

	// Virtual filesystem structure.
	'structure' => [
		'cache' => [
			'wp-rocket' => [
				'example.org'                             => [
					'index.html'      => '',
					'index.html_gzip' => '',
				],
				'example.org-wpmedia-594d03f6ae698691165999' => [
					'index.html'      => '',
					'index.html_gzip' => '',
				],
				'example.org-Foo-594d03f6ae698691165999' => [
					'index.html'      => '',
					'index.html_gzip' => '',
				],
				'example.org-Baz-594d03f6ae698691165999' => [
					'index.html'      => '',
					'index.html_gzip' => '',
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
			'cache/wp-rocket/example.org-Foo-594d03f6ae698691165999',
			// Deleted user cache files.
			[
				'cache/wp-rocket/example.org-Foo-594d03f6ae698691165999/index.html',
				'cache/wp-rocket/example.org-Foo-594d03f6ae698691165999/index.html_gzip',
			],
		],
		[
			// Username.
			'wpmedia',
			// Directory.
			'cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999',
			// Deleted user cache files.
			[
				'cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/index.html',
				'cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/index.html_gzip',
			],
		],
		[
			// Username.
			'Baz',
			// Directory.
			'cache/wp-rocket/example.org-Baz-594d03f6ae698691165999',
			// Deleted user cache files.
			[
				'cache/wp-rocket/example.org-Baz-594d03f6ae698691165999/index.html',
				'cache/wp-rocket/example.org-Baz-594d03f6ae698691165999/index.html_gzip',
			],
		],
	],
];
