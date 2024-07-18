<?php

return [
	'vfs_dir' => 'wp-content/',

	'test_data' => [

		'testShouldCombineJsFiles' => [
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

			'config'   => [
				'cdn_host'          => [],
				'cdn_url'           => 'http://example.org',
				'site_url'          => 'http://example.org',
				'defer_all_js'      => false,
				'exclude_defer_js'  => [],
				'exclude_js_templates' => [],
			],
		],

		'testShouldCombineJsFilesWithoutjQueryWhenDeferSafeMode' => [
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
		<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
		<script>
		nonce = "nonce";
		</script>
	</head>
	<body>
		<script src="http://example.org/wp-content/cache/min/1/cfe4eede7af9db7e8fa8951ee9782333.js" data-minify="1"></script>
	</body>
</html>
EXPECTED_HTML
				,

				'files' => [
					'wp-content/cache/min/1/cfe4eede7af9db7e8fa8951ee9782333.js',
					'wp-content/cache/min/1/cfe4eede7af9db7e8fa8951ee9782333.js.gz',
				],
			],

			'config'   => [
				'cdn_host'          => [],
				'cdn_url'           => 'http://example.org',
				'site_url'          => 'http://example.org',
				'defer_all_js'      => true,
				'exclude_defer_js'  => [
					'/wp-includes/js/jquery/jquery.js',
				],
				'exclude_js_templates' => [],
			],
		],

		'testShouldCombineJsFilesExceptExcluded' => [
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

			'config'   => [
				'cdn_host'          => [],
				'cdn_url'           => 'http://example.org',
				'site_url'          => 'http://example.org',
				'defer_all_js'      => false,
				'exclude_defer_js'  => [],
				'exclude_js_templates' => [],
			],
		],

		'testShouldCombineJsFilesExceptDelayed' => [
			'original' => <<<ORIGINAL_HTML
<html>
	<head>
		<title>Sample Page</title>
		<script type="text/javascript" data-rocketlazyloadscript="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
		<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>
		<script>
		document.getElementById("demo").innerHTML = "Hello JavaScript!";
		</script>
		<script data-rocketlazyloadscript="data:text/javascript;base64,YWxlcnQoIkJlIGFsZXJ0ISBXZSBuZWVkIG1vcmUgbGVydHMhIik7"></script>
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
		<script type="text/javascript" data-rocketlazyloadscript="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
		<script data-rocketlazyloadscript="data:text/javascript;base64,YWxlcnQoIkJlIGFsZXJ0ISBXZSBuZWVkIG1vcmUgbGVydHMhIik7"></script>
		<script>
		nonce = "nonce";
		</script>
	</head>
	<body>
<script src="http://example.org/wp-content/cache/min/1/790c5ff3a394635b5136e2e9409bacbc.js" data-minify="1">
</script>
	</body>
</html>
EXPECTED_HTML
				,

				'files' => [
					'wp-content/cache/min/1/790c5ff3a394635b5136e2e9409bacbc.js',
					'wp-content/cache/min/1/790c5ff3a394635b5136e2e9409bacbc.js.gz',
				],
			],

			'config'   => [
				'cdn_host'          => [],
				'cdn_url'           => 'http://example.org',
				'site_url'          => 'http://example.org',
				'defer_all_js'      => false,
				'exclude_defer_js'  => [],
				'exclude_js_templates' => [],
			],
		],

		'testShouldCombineJsFilesAndUseCdnUrl' => [
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

			'config'   => [
				'cdn_host' => [ '123456.rocketcdn.me' ],
				'cdn_url'  => 'https://123456.rocketcdn.me',
				'site_url' => 'http://example.org',
				'defer_all_js'      => false,
				'exclude_defer_js'  => [],
				'exclude_js_templates' => [],
			],
		],

		'testShouldCombineJsFilesWhenCdnUrl' => [
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

			'config'   => [
				'cdn_host' => [ '123456.rocketcdn.me' ],
				'cdn_url'  => 'https://123456.rocketcdn.me',
				'site_url' => 'http://example.org',
				'defer_all_js'      => false,
				'exclude_defer_js'  => [],
				'exclude_js_templates' => [],
			],
		],

		'testShouldCombineCssFilesWhenCdnUrlWithSubdir' => [
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

			'config'   => [
				'cdn_host' => [ '123456.rocketcdn.me/cdnpath' ],
				'cdn_url'  => 'https://123456.rocketcdn.me/cdnpath',
				'site_url' => 'http://example.org',
				'defer_all_js'      => false,
				'exclude_defer_js'  => [],
				'exclude_js_templates' => [],
			],
		],
	],
];
