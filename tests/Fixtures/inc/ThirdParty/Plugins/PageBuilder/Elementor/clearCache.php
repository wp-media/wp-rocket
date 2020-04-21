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
		'DoNothingWhenNotExternal' => [
			[
				'wp-content/cache/wp-rocket/example.org/index.html',
				'wp-content/cache/wp-rocket/example.org/index.html_gzip',
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html',
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html_gzip',
				'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css',
			],
			'internal',
			[
				'elementor/core/files/clear_cache',
				'update_option__elementor_global_css',
				'delete_option__element or_global_css',
			],
			true,
		],
		'ElementorClearCache' => [
			[
				'wp-content/cache/wp-rocket/example.org/index.html',
				'wp-content/cache/wp-rocket/example.org/index.html_gzip',
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html',
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html_gzip',
				'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css',
			],
			'external',
			[
				'elementor/core/files/clear_cache',
			],
			false,
		],
		'ElementorUpdateOption' => [
			[
				'wp-content/cache/wp-rocket/example.org/index.html',
				'wp-content/cache/wp-rocket/example.org/index.html_gzip',
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html',
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html_gzip',
				'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css',
			],
			'external',
			[
				'update_option__elementor_global_css',
			],
			false,
		],
		'ElementorDeleteOption' => [
			[
				'wp-content/cache/wp-rocket/example.org/index.html',
				'wp-content/cache/wp-rocket/example.org/index.html_gzip',
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html',
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/about/index.html_gzip',
				'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css',
			],
			'external',
			[
				'delete_option__elementor_global_css',
			],
			false,
		],
	],
];
