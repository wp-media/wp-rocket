<?php

$original_html = <<<ORIGINAL_HTML
<html>
	<head>
		<title>Sample Page</title>
		<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
		<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>
		<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
		<script type="module" src="http://example.org/wp-content/module.js"></script>
	</head>
	<body>
	</body>
</html>
ORIGINAL_HTML;


return [
	'vfs_dir' => 'wp-content/',

	'test_data' => [

		'minifyJsFiles' => [
			'original' => $original_html,
			'expected' => [
				'html'  => <<<EXPECTED_HTML
<html>
	<head>
		<title>Sample Page</title>
		<script data-minify="1" type="text/javascript" src="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js?ver={{mtime}}"></script>
		<script data-minify="1" type="text/javascript" src="http://example.org/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js?ver={{mtime}}"></script>
		<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
		<script type="module" src="http://example.org/wp-content/module.js"></script>
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js.gz',
				],
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
			'external_url' => '',
		],

		'minifyJssFileAndAddCdnCname' => [
			'original' => $original_html,
			'expected' => [
				'html' => <<<EXPECTED_HTML
<html>
	<head>
		<title>Sample Page</title>
		<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js?ver={{mtime}}"></script>
		<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js?ver={{mtime}}"></script>
		<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
		<script type="module" src="http://example.org/wp-content/module.js"></script>
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,

				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js.gz',
				],
			],

			'cdn_host' => [ '123456.rocketcdn.me' ],
			'cdn_url'  => 'https://123456.rocketcdn.me',
			'site_url' => 'http://example.org',
			'external_url' => '',
		],

		'minifyJsFilesWithCdnUrl' => [
			'original' => <<<ORIGINAL_HTML
<html>
	<head>
		<title>Sample Page</title>
		<script type="text/javascript" src="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/assets/script.js"></script>
		<script type="text/javascript" src="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/script.js"></script>
		<script type="text/javascript" src="https://123456.rocketcdn.me/wp-includes/js/jquery/jquery.js"></script>
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
		<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js?ver={{mtime}}"></script>
		<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js?ver={{mtime}}"></script>
		<script type="text/javascript" src="https://123456.rocketcdn.me/wp-includes/js/jquery/jquery.js"></script>
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,

				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js.gz',
				],
			],

			'cdn_host' => [ '123456.rocketcdn.me' ],
			'cdn_url'  => 'https://123456.rocketcdn.me',
			'site_url' => 'http://example.org',
			'external_url' => '',
		],

		'minifyJsFilesWithCdnUrlWithSubdir' => [
			'original' => <<<ORIGINAL_HTML
<html>
	<head>
		<title>Sample Page</title>
		<script type="text/javascript" src="https://123456.rocketcdn.me/cdnpath/wp-content/themes/twentytwenty/assets/script.js"></script>
		<script type="text/javascript" src="https://123456.rocketcdn.me/cdnpath/wp-content/plugins/hello-dolly/script.js"></script>
		<script type="text/javascript" src="https://123456.rocketcdn.me/cdnpath/wp-includes/js/jquery/jquery.js"></script>
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
		<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js?ver={{mtime}}"></script>
		<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js?ver={{mtime}}"></script>
		<script type="text/javascript" src="https://123456.rocketcdn.me/cdnpath/wp-includes/js/jquery/jquery.js"></script>
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,

				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script.js.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script.js.gz',
				],
			],

			'cdn_host' => [ '123456.rocketcdn.me/cdnpath' ],
			'cdn_url'  => 'https://123456.rocketcdn.me/cdnpath',
			'site_url' => 'http://example.org',
			'external_url' => '',
		],

		'minifyJsFilesWithExternalUrl' => [
			'original' => <<<ORIGINAL_HTML
<html>
	<head>
		<title>Sample Page</title>
		<script type="text/javascript" src="http://external-domain.org/path/to/external-script.js"></script>
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
		<script data-minify="1" type="text/javascript" src="http://example.org/wp-content/cache/min/1/path/to/external-script.js?ver={{mtime}}"></script>
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,

				'files' => [
					'wp-content/cache/min/1/path/to/external-script.js',
					'wp-content/cache/min/1/path/to/external-script.js.gz',
				],
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
			'external_url' => 'http://external-domain.org/path/to/external-script.js',
		],

		'minifyJsFilesWithExternalUrlWithValidIntegrityAttribute' => [
			'original' => <<<ORIGINAL_HTML
<html>
	<head>
		<title>Sample Page</title>
		<script type="text/javascript" src="http://external-domain.org/path/to/external-script.js"></script>
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
		<script data-minify="1" type="text/javascript" src="http://example.org/wp-content/cache/min/1/path/to/external-script.js?ver={{mtime}}"></script>
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,

				'files' => [
					'wp-content/cache/min/1/path/to/external-script.js',
					'wp-content/cache/min/1/path/to/external-script.js.gz',
				],
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
			'external_url' => 'http://external-domain.org/path/to/external-script.js',
			'has_integrity' => true,
			'valid_integrity' => true
		],

		'minifyJsFilesWithExternalUrlWithNotValidIntegrityAttribute' => [
			'original' => <<<ORIGINAL_HTML
<html>
	<head>
		<title>Sample Page</title>
		<script type="text/javascript" src="http://external-domain.org/path/to/external-script.js"></script>
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
		<script type="text/javascript" src="http://external-domain.org/path/to/external-script.js"></script>
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,

				'files' => [
				],
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
			'external_url' => 'http://external-domain.org/path/to/external-script.js',
			'has_integrity' => true,
			'valid_integrity' => false
		],

		'minifyJsFilesWithGoogleCSE' => [
			'original' => <<<ORIGINAL_HTML
<html>
	<head>
		<title>Sample Page</title>
		<script type="text/javascript" src="https://cse.google.com/cse.js?cx=xxx:xxx"></script>
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
		<script type="text/javascript" src="https://cse.google.com/cse.js?cx=xxx:xxx"></script>
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,

				'files' => [
				],
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
			'external_url' => 'https://cse.google.com/cse.js?cx=xxx:xxx',
		],
	],
];
