<?php
return [
	'vfs_dir'   => 'public/',
	'structure' => [
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
			[
				'html' => '<html>
					<head>
						<title>Sample Page</title>
						<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css" type="text/css" media="all">
						<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css">
						<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">
					</head>
					<body>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css.gz',
				],
			],
			[
				'minify_concatenate_css' => 0,
				'cdn'                => 0,
				'cdn_cnames'         => [],
				'cdn_zone'           => [],
			],
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
			[
				'html' => '<html>
					<head>
						<title>Sample Page</title>
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css" type="text/css" media="all">
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css">
						<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">
					</head>
					<body>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css.gz',
				],
			],
			[
				'minify_concatenate_css' => 0,
				'cdn'                => 1,
				'cdn_cnames'         => [
					'https://123456.rocketcdn.me',
				],
				'cdn_zone'           => [
					'all',
				],
			],
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
			[
				'html' => '<html>
					<head>
						<title>Sample Page</title>
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css" type="text/css" media="all">
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css">
						<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">
					</head>
					<body>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css.gz',
				],
			],
			[
				'minify_concatenate_css' => 0,
				'cdn'                => 1,
				'cdn_cnames'         => [
					'https://123456.rocketcdn.me',
				],
				'cdn_zone'           => [
					'all',
				],
			],
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
			[
				'html' => '<html>
					<head>
						<title>Sample Page</title>
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css" type="text/css" media="all">
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css">
						<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-includes/css/dashicons.min.css">
					</head>
					<body>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css.gz',
				],
			],
			[
				'minify_concatenate_css' => 0,
				'cdn'                => 1,
				'cdn_cnames'         => [
					'https://123456.rocketcdn.me/cdnpath',
				],
				'cdn_zone'           => [
					'all',
				],
			],
		],
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
						'<link rel="stylesheet" href="http://example.org/wp-content/cache/min/1/4e0f5a16e3462f854b9440920117e50e.css" data-minify="1" />' .
					'</head>' .
					'<body>' .
					'</body>' .
				'</html>',
				'files' => [
					'wp-content/cache/min/1/4e0f5a16e3462f854b9440920117e50e.css',
					'wp-content/cache/min/1/4e0f5a16e3462f854b9440920117e50e.css.gz',
				],
			],
			[
				'minify_concatenate_css' => 1,
				'cdn'                => 0,
				'cdn_cnames'         => [],
				'cdn_zone'           => [],
			],
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
						'<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/4e0f5a16e3462f854b9440920117e50e.css" data-minify="1" />' .
					'</head>' .
					'<body>' .
					'</body>' .
				'</html>',
				'files' => [
					'wp-content/cache/min/1/4e0f5a16e3462f854b9440920117e50e.css',
					'wp-content/cache/min/1/4e0f5a16e3462f854b9440920117e50e.css.gz',
				],
			],
			[
				'minify_concatenate_css' => 1,
				'cdn'                => 1,
				'cdn_cnames'         => [
					'https://123456.rocketcdn.me',
				],
				'cdn_zone'           => [
					'all',
				],
			],
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
						'<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/4e0f5a16e3462f854b9440920117e50e.css" data-minify="1" />' .
					'</head>' .
					'<body>' .
					'</body>' .
				'</html>',
				'files' => [
					'wp-content/cache/min/1/4e0f5a16e3462f854b9440920117e50e.css',
					'wp-content/cache/min/1/4e0f5a16e3462f854b9440920117e50e.css.gz',
				],
			],
			[
				'minify_concatenate_css' => 1,
				'cdn'                => 1,
				'cdn_cnames'         => [
					'https://123456.rocketcdn.me',
				],
				'cdn_zone'           => [
					'all',
				],
			],
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
						'<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/4e0f5a16e3462f854b9440920117e50e.css" data-minify="1" />' .
					'</head>' .
					'<body>' .
					'</body>' .
				'</html>',
				'files' => [
					'wp-content/cache/min/1/4e0f5a16e3462f854b9440920117e50e.css',
					'wp-content/cache/min/1/4e0f5a16e3462f854b9440920117e50e.css.gz',
				],
			],
			[
				'minify_concatenate_css' => 1,
				'cdn'                => 1,
				'cdn_cnames'         => [
					'https://123456.rocketcdn.me/cdnpath',
				],
				'cdn_zone'           => [
					'all',
				],
			],
		],
	],
];
