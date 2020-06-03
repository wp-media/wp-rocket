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
				           '<link rel="stylesheet" href="http://example.org/wp-content/cache/min/1/a54ca943da1ec83ba10d76eb4526ac1a.css" data-minify="1" />' .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/a54ca943da1ec83ba10d76eb4526ac1a.css',
					'wp-content/cache/min/1/a54ca943da1ec83ba10d76eb4526ac1a.css.gz',
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
				           '<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/a54ca943da1ec83ba10d76eb4526ac1a.css" data-minify="1" />' .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/a54ca943da1ec83ba10d76eb4526ac1a.css',
					'wp-content/cache/min/1/a54ca943da1ec83ba10d76eb4526ac1a.css.gz',
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
				           '<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/a54ca943da1ec83ba10d76eb4526ac1a.css" data-minify="1" />' .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/a54ca943da1ec83ba10d76eb4526ac1a.css',
					'wp-content/cache/min/1/a54ca943da1ec83ba10d76eb4526ac1a.css.gz',
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
				           '<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/a54ca943da1ec83ba10d76eb4526ac1a.css" data-minify="1" />' .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/a54ca943da1ec83ba10d76eb4526ac1a.css',
					'wp-content/cache/min/1/a54ca943da1ec83ba10d76eb4526ac1a.css.gz',
				],
			],

			'cdn_host' => [ '123456.rocketcdn.me/cdnpath' ],
			'cdn_url'  => 'https://123456.rocketcdn.me/cdnpath',
			'site_url' => 'http://example.org',
		],
	],
];
