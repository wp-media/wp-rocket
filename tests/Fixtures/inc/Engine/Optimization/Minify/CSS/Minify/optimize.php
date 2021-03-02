<?php

$original_html = <<<ORIGINAL_HTML
<html>
	<head>
		<title>Sample Page</title>
		<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">
		<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">
		<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">
		<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style-font-face.min.css">
	</head>
	<body>
	</body>
</html>
ORIGINAL_HTML;

return [
	'vfs_dir' => 'wp-content/',

	'test_data' => [

		'minifyCssFiles' => [
			'original' => $original_html,

			'expected' => [
				'html'  => <<<EXPECTED_HTML
<html>
	<head>
		<title>Sample Page</title>
		<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css?ver={{mtime}}" type="text/css" media="all">
		<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css?ver={{mtime}}">
		<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">
		<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css?ver={{mtime}}">
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css.gz',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css.gz',
				],
			],

			'cdn_host'     => [],
			'cdn_url'      => 'http://example.org',
			'site_url'     => 'http://example.org',
			'external_url' => '',
		],

		'minifyCssFilesWithRelativeURLs' => [
			'original' => <<<ORIGINAL_HTML
<html>
	<head>
		<title>Sample Page</title>
		<link rel="stylesheet" href="/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">
		<link rel="stylesheet" href="/wp-content/plugins/hello-dolly/style.css">
		<link rel="stylesheet" href="/wp-includes/css/dashicons.min.css">
		<link rel="stylesheet" href="/wp-content/themes/twentytwenty/style-font-face.min.css">
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
		<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css?ver={{mtime}}" type="text/css" media="all">
		<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css?ver={{mtime}}">
		<link rel="stylesheet" href="/wp-includes/css/dashicons.min.css">
		<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css?ver={{mtime}}">
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css.gz',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css.gz',
				],
			],

			'cdn_host'     => [],
			'cdn_url'      => 'http://example.org',
			'site_url'     => 'http://example.org',
			'external_url' => '',
		],

		'minifyCssFileAndAddCdnCname' => [
			'original' => $original_html,
			'expected' => [
				'html' => <<<EXPECTED_HTML
<html>
	<head>
		<title>Sample Page</title>
		<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css?ver={{mtime}}" type="text/css" media="all">
		<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css?ver={{mtime}}">
		<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">
		<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css?ver={{mtime}}">
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,

				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css.gz',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css.gz',
				],
			],

			'cdn_host'     => [ '123456.rocketcdn.me' ],
			'cdn_url'      => 'https://123456.rocketcdn.me',
			'site_url'     => 'http://example.org',
			'external_url' => '',
		],

		'minifyCssFilesWithCdnUrl' => [
			'original' => <<<ORIGINAL_HTML
<html>
	<head>
		<title>Sample Page</title>
		<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">
		<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/style.css">
		<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">
		<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/style-font-face.min.css">
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
		<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css?ver={{mtime}}" type="text/css" media="all">
		<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css?ver={{mtime}}">
		<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">
		<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css?ver={{mtime}}">
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,

				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css.gz',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css.gz',
				],
			],

			'cdn_host'     => [ '123456.rocketcdn.me' ],
			'cdn_url'      => 'https://123456.rocketcdn.me',
			'site_url'     => 'http://example.org',
			'external_url' => '',
		],

		'minifyCssFilesWithCdnUrlWithSubdir' => [
			'original' => <<<ORIGINAL_HTML
<html>
	<head>
		<title>Sample Page</title>
		<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">
		<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/plugins/hello-dolly/style.css">
		<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-includes/css/dashicons.min.css">
		<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/themes/twentytwenty/style-font-face.min.css">
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
		<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css?ver={{mtime}}" type="text/css" media="all">
		<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css?ver={{mtime}}">
		<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-includes/css/dashicons.min.css">
		<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css?ver={{mtime}}">
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css.gz',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css.gz',
				],
			],

			'cdn_host'     => [ '123456.rocketcdn.me/cdnpath' ],
			'cdn_url'      => 'https://123456.rocketcdn.me/cdnpath',
			'site_url'     => 'http://example.org',
			'external_url' => '',
		],

		'minifyExternalCssFiles' => [
			'original' => <<<ORIGINAL_HTML
<html>
	<head>
		<title>Sample Page</title>
		<link rel="stylesheet" href="http://external-domain.org/path/to/style.css" type="text/css" media="all">
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
		<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/path/to/style.css?ver={{mtime}}" type="text/css" media="all">
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,
				'files' => [
					'wp-content/cache/min/1/path/to/style.css',
					'wp-content/cache/min/1/path/to/style.css.gz',
				],
			],

			'cdn_host'     => [],
			'cdn_url'      => 'http://example.org',
			'site_url'     => 'http://example.org',
			'external_url' => 'http://external-domain.org/path/to/style.css',
		],

		'minifyExternalCssFilesWithValidIntegrityAttribute' => [
			'original' => <<<ORIGINAL_HTML
<html>
	<head>
		<title>Sample Page</title>
		<link rel="stylesheet" href="http://external-domain.org/path/to/style.css" integrity="sha384-Iwk3Na27oumffZOWcRt56FelXSzZqulFKATFo2oGfWyNRov+XJlD798hbG25kbVd" type="text/css" media="all">
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
		<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/path/to/style.css?ver={{mtime}}" type="text/css" media="all">
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,
				'files' => [
					'wp-content/cache/min/1/path/to/style.css',
					'wp-content/cache/min/1/path/to/style.css.gz',
				],
			],

			'cdn_host'        => [],
			'cdn_url'         => 'http://example.org',
			'site_url'        => 'http://example.org',
			'external_url'    => 'http://external-domain.org/path/to/style.css',
			'excluded_css'		=> [],
			'has_integrity'   => true,
			'valid_integrity' => true
		],

		'minifyExternalCssFilesWithNotValidIntegrityAttribute' => [
			'original' => <<<ORIGINAL_HTML
<html>
	<head>
		<title>Sample Page</title>
		<link rel="stylesheet" href="http://external-domain.org/path/to/style.css" integrity="sha384-Notvalid" type="text/css" media="all">
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
		<link rel="stylesheet" href="http://external-domain.org/path/to/style.css" integrity="sha384-Notvalid" type="text/css" media="all">
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,
				'files' => [
				],
			],

			'cdn_host'        => [],
			'cdn_url'         => 'http://example.org',
			'site_url'        => 'http://example.org',
			'external_url'    => 'http://external-domain.org/path/to/style.css',
			'excluded_css'		=> [],
			'has_integrity'   => true,
			'valid_integrity' => false
		],

		'excludeExternalCssFilesInExcludedCSSList' => [
			'original' => <<<ORIGINAL_HTML
<html>
	<head>
		<title>Sample Page</title>
		<link rel="stylesheet" href="http://excluded-external-domain.org/path/to/style.css" type="text/css" media="all">
		<link rel="stylesheet" href="http://subdomain.excluded-domain.org/path/to/style.css" type="text/css" media="all">
		<link rel="stylesheet" href="http://another-excluded-domain.org/path/to/style.css" type="text/css" media="all">
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
		<link rel="stylesheet" href="http://excluded-external-domain.org/path/to/style.css" type="text/css" media="all">
		<link rel="stylesheet" href="http://subdomain.excluded-domain.org/path/to/style.css" type="text/css" media="all">
		<link rel="stylesheet" href="http://another-excluded-domain.org/path/to/style.css" type="text/css" media="all">
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,
				'files' => [
				],
			],

			'cdn_host'     => [],
			'cdn_url'      => 'http://example.org',
			'site_url'     => 'http://example.org',
			'external_url' => 'http://external-domain.org/path/to/style.css',
			'excluded_css' => [
				'urls' => [
					'http://excluded-external-domain.org/path/to/style.css',
					'http://subdomain.excluded-domain.org/path/to/style.css',
					'http://another-excluded-domain.org/path/to/style.css',
				],
				'excluded_terms' => [
					'excluded-external-domain.org/path/to/style.css',
					'subdomain.excluded-domain.org/path/to/style.css',
					'another-excluded-domain.org',
				],
			],
		],
	],
];
