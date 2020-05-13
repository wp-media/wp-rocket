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
						'about'           => [
							'index.html'             => '',
							'index.html_gzip'        => '',
							'index-mobile.html'      => '',
							'index-mobile.html_gzip' => '',
						],
						'category'        => [
							'wordpress' => [
								'index.html'      => '',
								'index.html_gzip' => '',
							],
						],
						'blog'            => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
						'en'              => [
							'index.html'      => '',
							'index.html_gzip' => '',
						],
					],
					'example.org-wpmedia-594d03f6ae698691165999' => [
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
			// Expiration time
			'1 hour ago',
			// Lifespan in seconds = 50 minutes
			( 50 * 60 ),
			// Expired files.
			[],
			// Deleted directories.
			[]
		],
		[
			// Expiration time
			'11 hours ago',
			// Lifespan in seconds = 11 hours - 10 minutes.
			( 11 * 3600 ) - ( 10 * 60 ),
			// Expired files.
			[
				'wp-content/cache/wp-rocket/example.org/about/index.html',
				'wp-content/cache/wp-rocket/example.org/blog/index.html_gzip',
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/index.html',
				'wp-content/cache/wp-rocket/example.org/en/index.html',
			],
			// Deleted directories.
			[]
		],
		[
			// Expiration time
			'2 days ago',
			// Lifespan in seconds = 2 days - 30 minutes.
			( 2 * 86400 ) - ( 60 * 30 ),
			// Expired files.
			[
				'wp-content/cache/wp-rocket/example.org/en/index.html',
				'wp-content/cache/wp-rocket/example.org/category/wordpress/index.html',
				'wp-content/cache/wp-rocket/example.org/category/wordpress/index.html_gzip',
			],
			// Deleted directories.
			[
				'wp-content/cache/wp-rocket/example.org/category/wordpress/',
			]
		],
		[
			// Expiration time
			'2 weeks ago',
			// Lifespan in seconds = 13 days.
			13 * 86400,
			// Expired files.
			[
				'wp-content/cache/wp-rocket/example.org/about/index-mobile.html_gzip',
				'wp-content/cache/wp-rocket/example.org/en/index.html',
				'wp-content/cache/wp-rocket/example.org/en/index.html_gzip',
				'wp-content/cache/wp-rocket/example.org/category/wordpress/index.html',
				'wp-content/cache/wp-rocket/example.org/category/wordpress/index.html_gzip',
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/index.html',
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/index.html_gzip',
			],
			// Deleted directories.
			[
				'wp-content/cache/wp-rocket/example.org/en/',
				'wp-content/cache/wp-rocket/example.org/category/wordpress/',
				'wp-content/cache/wp-rocket/example.org-wpmedia-594d03f6ae698691165999/',
			]
		],
	],
];
