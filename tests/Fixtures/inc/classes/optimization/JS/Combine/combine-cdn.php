<?php

// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet

return [
	// Combine JS files
	[
		// Test Data: Original JS files.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>' .
				'<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>' .
				'<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>' .
				'<script>
				document.getElementById("demo").innerHTML = "Hello JavaScript!";
				</script>' .
				'<script>' .
				'nonce = "nonce";' .
				'</script>' .
			'</head>' .
			'<body>' .
			'</body>' .
		'</html>',
		// Expected: Combined JS files.
		'<html>' .
			'<head>' .
				'<title>Sample Page</title>' .
				'<script>' .
				'nonce = "nonce";' .
				'</script>' .
			'</head>' .
			'<body>' .
				'<script src="https://123456.rocketcdn.me/wp-content/cache/min/1/40aa0e42de6db86591cbab276ebb3586.js" data-minify="1"></script>' .
			'</body>' .
		'</html>',
	],
];
