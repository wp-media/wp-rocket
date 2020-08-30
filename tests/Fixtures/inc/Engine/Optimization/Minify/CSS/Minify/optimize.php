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
		<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-dcd1a95e5d432b5d300d7c2f216d7150.css" type="text/css" media="all">
		<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-b35546733b0295036e79cc1f700b1efd.css">
		<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">
		<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-73003a856907a7e09cca97c586493ed7.css">
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-dcd1a95e5d432b5d300d7c2f216d7150.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-dcd1a95e5d432b5d300d7c2f216d7150.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-b35546733b0295036e79cc1f700b1efd.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-b35546733b0295036e79cc1f700b1efd.css.gz',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-73003a856907a7e09cca97c586493ed7.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-73003a856907a7e09cca97c586493ed7.css.gz',
				],
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
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
ORIGINAL_HTML,

			'expected' => [
				'html'  => <<<EXPECTED_HTML
<html>
	<head>
		<title>Sample Page</title>
		<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-81368ec770e103151d0a6b86fb40c04b.css" type="text/css" media="all">
		<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-dd6c273e12758644d0d561ae5eb1792c.css">
		<link rel="stylesheet" href="/wp-includes/css/dashicons.min.css">
		<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-dae742d87623d4edfaef3856b672fab7.css">
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-81368ec770e103151d0a6b86fb40c04b.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-81368ec770e103151d0a6b86fb40c04b.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-dd6c273e12758644d0d561ae5eb1792c.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-dd6c273e12758644d0d561ae5eb1792c.css.gz',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-dae742d87623d4edfaef3856b672fab7.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-dae742d87623d4edfaef3856b672fab7.css.gz',
				],
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
			'external_url' => '',
		],

		'minifyCssFileAndAddCdnCname' => [
			'original' => $original_html,
			'expected' => [
				'html' => <<<EXPECTED_HTML
<html>
	<head>
		<title>Sample Page</title>
		<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-dcd1a95e5d432b5d300d7c2f216d7150.css" type="text/css" media="all">
		<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-b35546733b0295036e79cc1f700b1efd.css">
		<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">
		<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-73003a856907a7e09cca97c586493ed7.css">
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,

				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-dcd1a95e5d432b5d300d7c2f216d7150.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-dcd1a95e5d432b5d300d7c2f216d7150.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-b35546733b0295036e79cc1f700b1efd.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-b35546733b0295036e79cc1f700b1efd.css.gz',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-73003a856907a7e09cca97c586493ed7.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-73003a856907a7e09cca97c586493ed7.css.gz',
				],
			],

			'cdn_host' => [ '123456.rocketcdn.me' ],
			'cdn_url'  => 'https://123456.rocketcdn.me',
			'site_url' => 'http://example.org',
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
		<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-dcd1a95e5d432b5d300d7c2f216d7150.css" type="text/css" media="all">
		<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-b35546733b0295036e79cc1f700b1efd.css">
		<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">
		<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-73003a856907a7e09cca97c586493ed7.css">
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,

				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-dcd1a95e5d432b5d300d7c2f216d7150.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-dcd1a95e5d432b5d300d7c2f216d7150.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-b35546733b0295036e79cc1f700b1efd.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-b35546733b0295036e79cc1f700b1efd.css.gz',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-73003a856907a7e09cca97c586493ed7.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-73003a856907a7e09cca97c586493ed7.css.gz',
				],
			],

			'cdn_host' => [ '123456.rocketcdn.me' ],
			'cdn_url'  => 'https://123456.rocketcdn.me',
			'site_url' => 'http://example.org',
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
		<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-dcd1a95e5d432b5d300d7c2f216d7150.css" type="text/css" media="all">
		<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-b35546733b0295036e79cc1f700b1efd.css">
		<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-includes/css/dashicons.min.css">
		<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-73003a856907a7e09cca97c586493ed7.css">
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-dcd1a95e5d432b5d300d7c2f216d7150.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-dcd1a95e5d432b5d300d7c2f216d7150.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-b35546733b0295036e79cc1f700b1efd.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-b35546733b0295036e79cc1f700b1efd.css.gz',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-73003a856907a7e09cca97c586493ed7.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-73003a856907a7e09cca97c586493ed7.css.gz',
				],
			],

			'cdn_host' => [ '123456.rocketcdn.me/cdnpath' ],
			'cdn_url'  => 'https://123456.rocketcdn.me/cdnpath',
			'site_url' => 'http://example.org',
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
		<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/path/to/style-83936e8415166d4e08a1d8998b5990cd.css" type="text/css" media="all">
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,
				'files' => [
					'wp-content/cache/min/1/path/to/style-83936e8415166d4e08a1d8998b5990cd.css',
					'wp-content/cache/min/1/path/to/style-83936e8415166d4e08a1d8998b5990cd.css.gz',
				],
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
			'external_url' => 'http://external-domain.org/path/to/style.css',
		],
	],
];
