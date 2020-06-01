<?php
return [
	'vfs_dir' => 'wp-content/cache/wp-rocket/',
	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org'                => [
						'index.html_gzip' => '',
						'index.html' => '',
						'index-test.html' => '',
						'.mobile-active' => '',
						'.no-webp' => ''
					],
					'example.org-wpmedia-123456' => [
						'index.html_gzip' => '',
					],
					'example.org-tester-987654'  => [
						'index.html_gzip' => '',
					],

					'baz.example.org'             => [
						'index.html_gzip' => '',
					],
					'baz.example.org-baz1-123456' => [
						'index.html_gzip' => '',
					],
					'baz.example.org-baz2-987654' => [
						'index.html_gzip' => '',
					],
					'baz.example.org-baz3-456789' => [
						'index.html_gzip' => '',
					],

					'wp.baz.example.org'               => [
						'index.html_gzip' => '',
					],
					'wp.baz.example.org-wpbaz1-123456' => [
						'index.html_gzip' => '',
					],

					'example.org#fr' => [
						'index.html_gzip' => '',
					],
				],
			],
		],
	],
	'test_data' => [
		'testShouldCleanHomeFeeds' => [
			'config' => [],
			'expected' => [

			],
		]
	],
];
