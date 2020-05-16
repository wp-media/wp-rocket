<?php

return [
	'vfs_dir' => 'wp-content/',

	'test_data' => [

		'combineJsFiles' => [
			'original' => <<<ORIGINAL_HTML
<html>
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
</html>
ORIGINAL_HTML
			,

			'expected' => [
				'html' => <<<EXPECTED_HTML
<html>
	<head>
		<title>Sample Page</title>
		<script>
		nonce = "nonce";
		</script>
	</head>
	<body>
		<script src="http://example.org/wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js" data-minify="1"></script>
	</body>
</html>
EXPECTED_HTML
				,

				'files' => [
					'wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js',
					'wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js.gz',
				],
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
		],

		'combineJsFiles_andUseCdnUrl' => [
			'original' => <<<ORIGINAL_HTML
<html>
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
</html>
ORIGINAL_HTML
			,

			'expected' => [
				'html' => <<<EXPECTED_HTML
<html>
	<head>
		<title>Sample Page</title>
		<script>
		nonce = "nonce";
		</script>
	</head>
	<body>
		<script src="https://123456.rocketcdn.me/wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js" data-minify="1"></script>
	</body>
</html>
EXPECTED_HTML
				,

				'files' => [
					'wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js',
					'wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js.gz',
				],
			],

			'cdn_host' => [ '123456.rocketcdn.me' ],
			'cdn_url'  => 'https://123456.rocketcdn.me',
			'site_url' => 'http://example.org',
		],

		'combineJsFiles_whenCdnUrl' => [
			'original' => <<<ORIGINAL_HTML
<html>
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
</html>
ORIGINAL_HTML
			,

			'expected' => [
				'html'  => <<<EXPECTED_HTML
<html>
	<head>
		<title>Sample Page</title>
		<script>
		nonce = "nonce";
		</script>
	</head>
	<body>
		<script src="https://123456.rocketcdn.me/wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js" data-minify="1"></script>
	</body>
</html>
EXPECTED_HTML
				,
				'files' => [
					'wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js',
					'wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js.gz',
				],
			],

			'cdn_host' => [ '123456.rocketcdn.me' ],
			'cdn_url'  => 'https://123456.rocketcdn.me',
			'site_url' => 'http://example.org',
		],

		'combineCssFiles_whenCdnUrlWithSubdir' => [
			'original' => <<<ORIGINAL_HTML
<html>
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
</html>
ORIGINAL_HTML
			,

			'expected' => [
				'html'  => <<<EXPECTED_HTML
<html>
	<head>
		<title>Sample Page</title>
		<script>
		nonce = "nonce";
		</script>
	</head>
	<body>
		<script src="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js" data-minify="1"></script>
	</body>
</html>
EXPECTED_HTML
				,
				'files' => [
					'wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js',
					'wp-content/cache/min/1/900b339c19ff5a927b3311bf5ddb4dfd.js.gz',
				],
			],

			'cdn_host' => [ '123456.rocketcdn.me/cdnpath' ],
			'cdn_url'  => 'https://123456.rocketcdn.me/cdnpath',
			'site_url' => 'http://example.org',
		],
	],
];
