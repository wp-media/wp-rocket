<?php
return [
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
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<link rel="stylesheet" href="http://example.org/wp-content/cache/min/1/468169e0b2801936e9dbb849292a541a.css" data-minify="1" />' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
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
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/468169e0b2801936e9dbb849292a541a.css" data-minify="1" />' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
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
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/468169e0b2801936e9dbb849292a541a.css" data-minify="1" />' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
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
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/468169e0b2801936e9dbb849292a541a.css" data-minify="1" />' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
		[
			'123456.rocketcdn.me/cdnpath'
		],
		'https://123456.rocketcdn.me/cdnpath',
		'http://example.org',
	],
];
