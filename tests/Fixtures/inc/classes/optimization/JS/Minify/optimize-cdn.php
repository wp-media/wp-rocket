<?php

// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet

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
				'<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-589f68a39d38f437b5183379f7140347.js"></script>' .
				'<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-1660ca767e5d3392e65c0813f6f3a264.js"></script>' .
				'<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-includes/js/jquery/jquery-c9eeccad7a0ad8b9ca4b57d493258a2d.js"></script>' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
	],
];
