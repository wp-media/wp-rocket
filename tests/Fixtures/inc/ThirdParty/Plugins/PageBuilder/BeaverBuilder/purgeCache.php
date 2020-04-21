<?php
return [
	'vfs_dir'   => 'wp-content/',
	'structure' => [
		'wp-content' => [
			'cache' => [
				'min'          => [
					'1' => [
						'5c795b0e3a1884eec34a989485f863ff.js'     => '',
						'fa2965d41f1515951de523cecb81f85e.css'    => '',
					],
				],
				'wp-rocket'    => [
					'example.org' => [
						'index.html'      => '',
						'index.html_gzip' => '',
					],
					'example.org-wpmedia-594d03f6ae698691165999' => [
						'about' => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
					],
				],
			],
		],
	],
	'test_data' => [
		'FlBuilderBeforeSaveLayout' => [
			'fl_builder_before_save_layout',
			[
				'wp-content/cache/wp-rocket/example.org/index.html',
				'wp-content/cache/wp-rocket/example.org/index.html_gzip',
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html',
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html_gzip',
				'wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js',
				'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css',
			],
		],
		'FlBuilderCacheCleared' => [
			'fl_builder_cache_cleared',
			[
				'wp-content/cache/wp-rocket/example.org/index.html',
				'wp-content/cache/wp-rocket/example.org/index.html_gzip',
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html',
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html_gzip',
				'wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js',
				'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css',
			],
		],
	],
];
