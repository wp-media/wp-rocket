<?php

return [
	'vfs_dir' => 'wp-content/',

	'test_data' => [

		'combineCssFiles' => [
			'original' =>
				'<html><head><title>Sample Page</title>' .
				'<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
				'<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">' .
				'<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">' .
				'</head><body></body></html>',

			'expected' => [
				'html'  => '<html><head><title>Sample Page</title>' .
				           '<link rel="stylesheet" href="http://example.org/wp-content/cache/min/1/a474f99d5c9b770f1e3571c39b4ae4b6.css" media="all" data-minify="1" />' .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/a474f99d5c9b770f1e3571c39b4ae4b6.css',
					'wp-content/cache/min/1/a474f99d5c9b770f1e3571c39b4ae4b6.css.gz',
				],
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
		],

		'combineCssFilesWithExternalCSS' => [
			'original' =>
				'<html><head><title>Sample Page</title>' .
				'<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
				'<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">' .
				'<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">' .
				'<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" media="all" />' .
				'</head><body></body></html>',

			'expected' => [
				'html'  => '<html><head><title>Sample Page</title>' .
				           '<link rel="stylesheet" href="http://example.org/wp-content/cache/min/1/8c71d2e9bb94d96d6f6e83744b1c1745.css" media="all" data-minify="1" />' .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/8c71d2e9bb94d96d6f6e83744b1c1745.css',
					'wp-content/cache/min/1/8c71d2e9bb94d96d6f6e83744b1c1745.css.gz',
				],
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
		],

		'combineCssFiles_andUseCdnUrl' => [
			'original' => '<html><head><title>Sample Page</title>' .
			              '<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
			              '<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">' .
			              '<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">' .
			              '</head><body></body></html>',

			'expected' => [
				'html'  => '<html><head><title>Sample Page</title>' .
				           '<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/a474f99d5c9b770f1e3571c39b4ae4b6.css" media="all" data-minify="1" />' .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/a474f99d5c9b770f1e3571c39b4ae4b6.css',
					'wp-content/cache/min/1/a474f99d5c9b770f1e3571c39b4ae4b6.css.gz',
				],
			],

			'cdn_host' => [ '123456.rocketcdn.me' ],
			'cdn_url'  => 'https://123456.rocketcdn.me',
			'site_url' => 'http://example.org',
		],

		'combineCssFiles_whenCdnUrl' => [
			'original' => '<html><head><title>Sample Page</title>' .
			              '<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
			              '<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/style.css">' .
			              '<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">' .
			              '</head><body></body></html>',

			'expected' => [
				'html'  => '<html><head><title>Sample Page</title>' .
				           '<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/9257670dda564cf9413d31cb2a0dc089.css" media="all" data-minify="1" />' .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/9257670dda564cf9413d31cb2a0dc089.css',
					'wp-content/cache/min/1/9257670dda564cf9413d31cb2a0dc089.css.gz',
				],
			],

			'cdn_host' => [ '123456.rocketcdn.me' ],
			'cdn_url'  => 'https://123456.rocketcdn.me',
			'site_url' => 'http://example.org',
		],

		'combineCssFiles_whenCdnUrlWithSubdir' => [
			'original' => '<html><head><title>Sample Page</title>' .
			              '<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
			              '<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/plugins/hello-dolly/style.css">' .
			              '<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-includes/css/dashicons.min.css">' .
			              '</head><body></body></html>',

			'expected' => [
				'html'  => '<html><head><title>Sample Page</title>' .
				           '<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/a1a6011a8f78cb98deb15153d5614be7.css" media="all" data-minify="1" />' .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/a1a6011a8f78cb98deb15153d5614be7.css',
					'wp-content/cache/min/1/a1a6011a8f78cb98deb15153d5614be7.css.gz',
				],
			],

			'cdn_host' => [ '123456.rocketcdn.me/cdnpath' ],
			'cdn_url'  => 'https://123456.rocketcdn.me/cdnpath',
			'site_url' => 'http://example.org',
		],
	],
];
