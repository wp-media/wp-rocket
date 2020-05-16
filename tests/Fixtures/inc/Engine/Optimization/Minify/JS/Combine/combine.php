<?php
return [
	'vfs_dir'   => 'wordpress/',

	'test_data' => [
		// Combine JS files
		[
			// Test Data: Original JS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
					<script>
					document.getElementById("demo").innerHTML = "Hello JavaScript!";
					</script>
					<script>
					nonce = "nonce";
					</script>
				</head>
				<body>
				</body>
			</html>',
			// Expected: Combined JS files.
			[
				'html' => '<html>
					<head>
						<title>Sample Page</title>
						<script>
						nonce = "nonce";
						</script>
					</head>
					<body>
						<script src="http://example.org/wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js" data-minify="1"></script>
					</body>
				</html>',
				'files' => [
					'wordpress/wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js',
					'wordpress/wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js.gz',
				],
			],
			[],
			'http://example.org',
			'http://example.org',
		],
		// Combine JS files to CDN URL
		[
			// Test Data: Original JS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
					<script>
					document.getElementById("demo").innerHTML = "Hello JavaScript!";
					</script>
					<script>
					nonce = "nonce";
					</script>
				</head>
				<body>
				</body>
			</html>',
			// Expected: Combined JS files.
			[
				'html' => '<html>
					<head>
						<title>Sample Page</title>
						<script>
						nonce = "nonce";
						</script>
					</head>
					<body>
						<script src="https://123456.rocketcdn.me/wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js" data-minify="1"></script>
					</body>
				</html>',
				'files' => [
					'wordpress/wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js',
					'wordpress/wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js.gz',
				],
			],
			[
				'123456.rocketcdn.me',
			],
			'https://123456.rocketcdn.me',
			'http://example.org',
		],
		// Combine JS files with CDN URL
		[
			// Test Data: Original JS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="https://123456.rocketcdn.me/wp-includes/js/jquery/jquery.js"></script>
					<script>
					document.getElementById("demo").innerHTML = "Hello JavaScript!";
					</script>
					<script>
					nonce = "nonce";
					</script>
				</head>
				<body>
				</body>
			</html>',
			// Expected: Combined JS files.
			[
				'html' => '<html>
					<head>
						<title>Sample Page</title>
						<script>
						nonce = "nonce";
						</script>
					</head>
					<body>
						<script src="https://123456.rocketcdn.me/wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js" data-minify="1"></script>
					</body>
				</html>',
				'files' => [
					'wordpress/wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js',
					'wordpress/wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js.gz',
				],
			],
			[
				'123456.rocketcdn.me',
			],
			'https://123456.rocketcdn.me',
			'http://example.org',
		],
		// Combine JS files with CDN URL with subdirectory
		[
			// Test Data: Original JS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="https://123456.rocketcdn.me/cdnpath/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="https://123456.rocketcdn.me/cdnpath/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="https://123456.rocketcdn.me/cdnpath/wp-includes/js/jquery/jquery.js"></script>
					<script>
					document.getElementById("demo").innerHTML = "Hello JavaScript!";
					</script>
					<script>
					nonce = "nonce";
					</script>
				</head>
				<body>
				</body>
			</html>',
			// Expected: Combined JS files.
			[
				'html' => '<html>
					<head>
						<title>Sample Page</title>
						<script>
						nonce = "nonce";
						</script>
					</head>
					<body>
						<script src="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js" data-minify="1"></script>
					</body>
				</html>',
				'files' => [
					'wordpress/wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js',
					'wordpress/wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js.gz',
				],
			],
			[
				'123456.rocketcdn.me/cdnpath',
			],
			'https://123456.rocketcdn.me/cdnpath',
			'http://example.org',
		],
	],
];
