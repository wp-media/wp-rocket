<?php
return [
	// Minify JS files
	[
		// Test Data: Original JS files.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>' .
				'<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>' .
				'<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
		// Expected: Minified JS files.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<script data-minify="1" type="text/javascript" src="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-589f68a39d38f437b5183379f7140347.js"></script>' .
				'<script data-minify="1" type="text/javascript" src="http://example.org/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-1660ca767e5d3392e65c0813f6f3a264.js"></script>' .
				'<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
		[],
		'http://example.org',
	],
	// Minify JS files to CDN URL
	[
		// Test Data: Original JS files.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>' .
				'<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>' .
				'<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
		// Expected: Minified JS files.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-589f68a39d38f437b5183379f7140347.js"></script>' .
				'<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-1660ca767e5d3392e65c0813f6f3a264.js"></script>' .
				'<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
		[
			'123456.rocketcdn.me'
		],
		'https://123456.rocketcdn.me',
	],
	// Minify JS files with CDN URL.
	[
		// Test Data: Original JS files.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<script type="text/javascript" src="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/assets/script.js"></script>' .
				'<script type="text/javascript" src="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/script.js"></script>' .
				'<script type="text/javascript" src="https://123456.rocketcdn.me/wp-includes/js/jquery/jquery.js"></script>' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
		// Expected: Minified JS files.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-59c584b72126a84ab6c36728906f785d.js"></script>' .
				'<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-7c9838e8e45d9909b8e794d5675932aa.js"></script>' .
				'<script type="text/javascript" src="https://123456.rocketcdn.me/wp-includes/js/jquery/jquery.js"></script>' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
		[
			'123456.rocketcdn.me'
		],
		'https://123456.rocketcdn.me',
	],
];
