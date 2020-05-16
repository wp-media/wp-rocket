<?php
return [
	'vfs_dir'   => 'wordpress/',

	'test_data' => [
		// Minify JS files
		[
			'original' => '<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
				</head>
				<body>
				</body>
			</html>',

			'expected' => [
				'html' => '<html>
					<head>
						<title>Sample Page</title>
						<script data-minify="1" type="text/javascript" src="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js"></script>
						<script data-minify="1" type="text/javascript" src="http://example.org/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js"></script>
						<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
					</head>
					<body>
					</body>
				</html>',
				'files' => [
					'wordpress/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js',
					'wordpress/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js.gz',
					'wordpress/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js',
					'wordpress/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js.gz',
				],
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
		],
		// Minify JS files to CDN URL
		[
			// Test Data: Original JS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
				</head>
				<body>
				</body>
			</html>',
			// Expected: Minified JS files.
			[
				'html' => '<html>
					<head>
						<title>Sample Page</title>
						<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js"></script>
						<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js"></script>
						<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
					</head>
					<body>
					</body>
				</html>',
				'files' => [
					'wordpress/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js',
					'wordpress/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js.gz',
					'wordpress/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js',
					'wordpress/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js.gz',
				],
			],
			[
				'123456.rocketcdn.me'
			],
			'https://123456.rocketcdn.me',
			'http://example.org',
		],
		// Minify JS files with CDN URL.
		[
			// Test Data: Original JS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="https://123456.rocketcdn.me/wp-includes/js/jquery/jquery.js"></script>
				</head>
				<body>
				</body>
			</html>',
			// Expected: Minified JS files.
			[
				'html' => '<html>
					<head>
						<title>Sample Page</title>
						<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js"></script>
						<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js"></script>
						<script type="text/javascript" src="https://123456.rocketcdn.me/wp-includes/js/jquery/jquery.js"></script>
					</head>
					<body>
					</body>
				</html>',
				'files' => [
					'wordpress/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js',
					'wordpress/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js.gz',
					'wordpress/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js',
					'wordpress/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js.gz',
				],
			],
			[
				'123456.rocketcdn.me'
			],
			'https://123456.rocketcdn.me',
			'http://example.org',
		],
	],
];
