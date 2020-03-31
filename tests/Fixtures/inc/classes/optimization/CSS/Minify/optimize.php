<?php
return [
	'vfs_dir'   => 'public/',
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
		// Minify CSS files
		[
			// Test Data: Original CSS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">
					<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">
					<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">
				</head>
				<body>
				</body>
			</html>',
			// Expected: Minified CSS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-dcd1a95e5d432b5d300d7c2f216d7150.css" type="text/css" media="all">
					<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-b35546733b0295036e79cc1f700b1efd.css">
					<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">
				</head>
				<body>
				</body>
			</html>',
			[],
			'http://example.org',
			'http://example.org',
		],
		// Minify CSS files & add CDN CNAME.
		[
			// Test Data: Original CSS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">
					<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">
					<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">
				</head>
				<body>
				</body>
			</html>',
			// Expected: Minified CSS files with CDN
			'<html>
				<head>
					<title>Sample Page</title>
					<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-dcd1a95e5d432b5d300d7c2f216d7150.css" type="text/css" media="all">
					<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-b35546733b0295036e79cc1f700b1efd.css">
					<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">
				</head>
				<body>
				</body>
			</html>',
			[
				'123456.rocketcdn.me',
			],
			'https://123456.rocketcdn.me',
			'http://example.org',
		],
		// Minify CSS files with CDN URL already.
		[
			// Test Data: Original CSS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/style.css">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">
				</head>
				<body>
				</body>
			</html>',
			// Expected: Minified CSS files with CDN URL.
			'<html>
				<head>
					<title>Sample Page</title>
					<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-dcd1a95e5d432b5d300d7c2f216d7150.css" type="text/css" media="all">
					<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-b35546733b0295036e79cc1f700b1efd.css">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">
				</head>
				<body>
				</body>
			</html>',
			[
				'123456.rocketcdn.me',
			],
			'https://123456.rocketcdn.me',
			'http://example.org',
		],
		// Minify CSS files with CDN URL subdirectory already.
		[
			// Test Data: Original CSS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/plugins/hello-dolly/style.css">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-includes/css/dashicons.min.css">
				</head>
				<body>
				</body>
			</html>',
			// Expected: Minified CSS files with CDN URL.
			'<html>
				<head>
					<title>Sample Page</title>
					<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-dcd1a95e5d432b5d300d7c2f216d7150.css" type="text/css" media="all">
					<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-b35546733b0295036e79cc1f700b1efd.css">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-includes/css/dashicons.min.css">
				</head>
				<body>
				</body>
			</html>',
			[
				'123456.rocketcdn.me/cdnpath',
			],
			'https://123456.rocketcdn.me/cdnpath',
			'http://example.org',
		],
	],
];
