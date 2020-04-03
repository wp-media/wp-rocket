<?php
return [
	'vfs_dir' => 'public/',
	'structure' => [
		'wordpress' => [
			'wp-includes' => [
				'js' => [
					'jquery' => [
						'jquery.js' => 'jquery',
					],
				],
				'css' => [
					'dashicons.min.css' => '',
				],
			],
			'wp-content' => [
				'cache' => [
					'busting' => [
						'1' => [],
					],
				],
				'themes' => [
					'twentytwenty' => [
						'style.css' => 'test',
						'assets'    => [
							'script.js' => 'test',
						]
					]
				],
				'plugins' => [
					'hello-dolly' => [
						'style.css'  => 'test',
						'script.js' => 'test',
					]
				],
			],
		],
	],
	'test_data' => [
		// Styles are commented in HTML comments = ignored.
		[
			'<html>
				<head>
					<title>Page title</title>
					<!-- <link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css?ver=1.0"> -->
					<!-- <link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css?ver=3.5"> -->
					<script src="http://example.org/wp-includes/js/jquery/jquery.js?ver=5.3"></script>
				</head>
				<body>
					<script src="http://example.org/wp-content/plugins/hello-dolly/script.js?ver=3.5"></script>
				</body>
			</html>',
			'<html>
				<head>
					<title>Page title</title>
					<!-- <link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css?ver=1.0"> -->
					<!-- <link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css?ver=3.5"> -->
					<script src="http://example.org/wp-includes/js/jquery/jquery.js?ver=5.3"></script>
				</head>
				<body>
					<script src="http://example.org/wp-content/plugins/hello-dolly/script.js?ver=3.5"></script>
				</body>
			</html>',
			[],
			'http://example.org',
			'http://example.org',
		],
		// Default domain, CSS files are cached busted. Files with the WordPress version in the query get the ?ver= removed.
		[
			'<html>
				<head>
					<title>Page title</title>
					<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css?ver=5.3">
					<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css?ver=1.0">
					<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css?ver=3.5">
					<link rel="stylesheet" href="https://google.com/external.css?v=20200306">
					<script src="http://example.org/wp-includes/js/jquery/jquery.js?ver=5.3"></script>
					<script src="http://example.org/wp-content/themes/twentytwenty/assets/script.js?ver=1.0"></script>
				</head>
				<body>
					<script src="http://example.org/wp-content/plugins/hello-dolly/script.js?ver=3.5"></script>
				</body>
			</html>',
			'<html>
				<head>
					<title>Page title</title>
					<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">
					<link rel="stylesheet" href="http://example.org/wp-content/cache/busting/1/wp-content/themes/twentytwenty/style-1.0.css">
					<link rel="stylesheet" href="http://example.org/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/style-3.5.css">
					<link rel="stylesheet" href="https://google.com/external.css?v=20200306">
					<script src="http://example.org/wp-includes/js/jquery/jquery.js?ver=5.3"></script>
					<script src="http://example.org/wp-content/themes/twentytwenty/assets/script.js?ver=1.0"></script>
				</head>
				<body>
					<script src="http://example.org/wp-content/plugins/hello-dolly/script.js?ver=3.5"></script>
				</body>
			</html>',
			[],
			'http://example.org',
			'http://example.org',
		],
		// CDN URL, CSS files are cached busted. Files with the WordPress version in the query get the ?ver= removed.
		[
			'<html>
				<head>
					<title>Page title</title>
					<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css?ver=5.3">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/style.css?ver=1.0">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/style.css?ver=3.5">
					<link rel="stylesheet" href="https://google.com/external.css?v=20200306">
					<script src="https://123456.rocketcdn.me/wp-includes/js/jquery/jquery.js?ver=5.3"></script>
					<script src="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/assets/script.js?ver=1.0"></script>
				</head>
				<body>
					<script src="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/script.js?ver=3.5"></script>
				</body>
			</html>',
			'<html>
				<head>
					<title>Page title</title>
					<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/busting/1/wp-content/themes/twentytwenty/style-1.0.css">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/style-3.5.css">
					<link rel="stylesheet" href="https://google.com/external.css?v=20200306">
					<script src="https://123456.rocketcdn.me/wp-includes/js/jquery/jquery.js?ver=5.3"></script>
					<script src="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/assets/script.js?ver=1.0"></script>
				</head>
				<body>
					<script src="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/script.js?ver=3.5"></script>
				</body>
			</html>',
			[
				'123456.rocketcdn.me',
			],
			'https://123456.rocketcdn.me',
			'http://example.org',
		],
		// CDN URL with subdirectory, CSS files are cached busted. Files with the WordPress version in the query get the ?ver= removed.
		[
			'<html>
				<head>
					<title>Page title</title>
					<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-includes/css/dashicons.min.css?ver=5.3">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/themes/twentytwenty/style.css?ver=1.0">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/plugins/hello-dolly/style.css?ver=3.5">
					<link rel="stylesheet" href="https://google.com/external.css?v=20200306">
					<script src="https://123456.rocketcdn.me/cdnpath/wp-includes/js/jquery/jquery.js?ver=5.3"></script>
					<script src="https://123456.rocketcdn.me/cdnpath/wp-content/themes/twentytwenty/assets/script.js?ver=1.0"></script>
				</head>
				<body>
					<script src="https://123456.rocketcdn.me/cdnpath/wp-content/plugins/hello-dolly/script.js?ver=3.5"></script>
				</body>
			</html>',
			'<html>
				<head>
					<title>Page title</title>
					<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-includes/css/dashicons.min.css">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/busting/1/wp-content/themes/twentytwenty/style-1.0.css">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/style-3.5.css">
					<link rel="stylesheet" href="https://google.com/external.css?v=20200306">
					<script src="https://123456.rocketcdn.me/cdnpath/wp-includes/js/jquery/jquery.js?ver=5.3"></script>
					<script src="https://123456.rocketcdn.me/cdnpath/wp-content/themes/twentytwenty/assets/script.js?ver=1.0"></script>
				</head>
				<body>
					<script src="https://123456.rocketcdn.me/cdnpath/wp-content/plugins/hello-dolly/script.js?ver=3.5"></script>
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
