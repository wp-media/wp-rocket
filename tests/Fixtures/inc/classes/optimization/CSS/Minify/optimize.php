<?php
return [
	// Minify CSS files
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
		// Expected: Minified CSS files.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-0078288adc33938689f5e635129f87de.css" type="text/css" media="all">' .
				'<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-f4fb22c9952e98d6d6094c11205318ea.css">' .
				'<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
		[],
		'http://example.org',
	],
	// Minify CSS files & add CDN CNAME.
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
		// Expected: Minified CSS files with CDN
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-0078288adc33938689f5e635129f87de.css" type="text/css" media="all">' .
				'<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-f4fb22c9952e98d6d6094c11205318ea.css">' .
				'<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
		[
			'123456.rocketcdn.me',
		],
		'https://123456.rocketcdn.me',
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
				<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-9b026459e8187da696ce1fe8d56da747.css" type="text/css" media="all">
				<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-6d6706c772358ba782203730db662bd0.css">
				<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">
			</head>
			<body>
			</body>
		</html>',
		[
			'123456.rocketcdn.me',
		],
		'https://123456.rocketcdn.me',
	],
];
