<?php
return [
	'vfs_dir'   => 'wordpress/',

	'structure' => [
		'wordpress' => [
			'wp-includes' => [
				'js' => [
					'jquery' => [
						'jquery.js' => 'jquery',
					],
				],
				'css' => [
					'dashicons.min.css' => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
				],
			],
			'wp-content' => [
				'cache' => [
					'min' => [
						'1' => [],
					],
				],
				'themes' => [
					'twentytwenty' => [
						'style.css' => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
						'assets'    => [
							'script.js' => 'test',
						]
					]
				],
				'plugins' => [
					'hello-dolly' => [
						'style.css'  => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
						'script.js' => 'test',
					]
				],
			],
		],
	],

	'test_data' => [
		// Combine CSS files.
		[
			// Test Data: Original CSS files.
			'<html>' .
				'<head>' .
					'<title>Sample Page</title>' .
					'<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
					'<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">' .
					'<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">' .
				'</head>' .
				'<body>' .
				'</body>' .
			'</html>',
			// Expected: Combined CSS files.
			[
				'html' => '<html>' .
					'<head>' .
						'<title>Sample Page</title>' .
						'<link rel="stylesheet" href="http://example.org/wp-content/cache/min/1/0bca286481748a69cdbe9a6695015ec9.css" data-minify="1" />' .
					'</head>' .
					'<body>' .
					'</body>' .
				'</html>',
				'files' => [
					'wordpress/wp-content/cache/min/1/0bca286481748a69cdbe9a6695015ec9.css',
					'wordpress/wp-content/cache/min/1/0bca286481748a69cdbe9a6695015ec9.css.gz'
				],
			],
			[],
			'http://example.org',
			'http://example.org',
		],
		// Combine CSS files to CDN URL.
		[
			// Test Data: Original CSS files.
			'<html>' .
				'<head>' .
					'<title>Sample Page</title>' .
					'<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
					'<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">' .
					'<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">' .
				'</head>' .
				'<body>' .
				'</body>' .
			'</html>',
			// Expected: Combined CSS files.
			[
				'html' => '<html>' .
					'<head>' .
						'<title>Sample Page</title>' .
						'<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/0bca286481748a69cdbe9a6695015ec9.css" data-minify="1" />' .
					'</head>' .
					'<body>' .
					'</body>' .
				'</html>',
				'files' => [
					'wordpress/wp-content/cache/min/1/0bca286481748a69cdbe9a6695015ec9.css',
					'wordpress/wp-content/cache/min/1/0bca286481748a69cdbe9a6695015ec9.css.gz'
				],
			],
			[
				'123456.rocketcdn.me'
			],
			'https://123456.rocketcdn.me',
			'http://example.org',
		],
		// Combine CSS files with CDN URL.
		[
			// Test Data: Original CSS files.
			'<html>' .
				'<head>' .
					'<title>Sample Page</title>' .
					'<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
					'<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/style.css">' .
					'<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">' .
				'</head>' .
				'<body>' .
				'</body>' .
			'</html>',
			// Expected: Combined CSS files.
			[
				'html' => '<html>' .
					'<head>' .
						'<title>Sample Page</title>' .
						'<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/0bca286481748a69cdbe9a6695015ec9.css" data-minify="1" />' .
					'</head>' .
					'<body>' .
					'</body>' .
				'</html>',
				'files' => [
					'wordpress/wp-content/cache/min/1/0bca286481748a69cdbe9a6695015ec9.css',
					'wordpress/wp-content/cache/min/1/0bca286481748a69cdbe9a6695015ec9.css.gz'
				],
			],
			[
				'123456.rocketcdn.me'
			],
			'https://123456.rocketcdn.me',
			'http://example.org',
		],
		// Combine CSS files with CDN URL with subdirectory.
		[
			// Test Data: Original CSS files.
			'<html>' .
				'<head>' .
					'<title>Sample Page</title>' .
					'<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
					'<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/plugins/hello-dolly/style.css">' .
					'<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-includes/css/dashicons.min.css">' .
				'</head>' .
				'<body>' .
				'</body>' .
			'</html>',
			// Expected: Combined CSS files.
			[
				'html' => '<html>' .
					'<head>' .
						'<title>Sample Page</title>' .
						'<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/0bca286481748a69cdbe9a6695015ec9.css" data-minify="1" />' .
					'</head>' .
					'<body>' .
					'</body>' .
				'</html>',
				'files' => [
					'wordpress/wp-content/cache/min/1/0bca286481748a69cdbe9a6695015ec9.css',
					'wordpress/wp-content/cache/min/1/0bca286481748a69cdbe9a6695015ec9.css.gz'
				],
			],
			[
				'123456.rocketcdn.me/cdnpath'
			],
			'https://123456.rocketcdn.me/cdnpath',
			'http://example.org',
		],
	],
];
