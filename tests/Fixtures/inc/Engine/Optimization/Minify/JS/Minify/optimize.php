<?php

$original_html = <<<ORIGINAL_HTML
<html>
	<head>
		<title>Sample Page</title>
		<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
		<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>
		<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
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
		<script data-minify="1" type="text/javascript" src="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js"></script>
		<script data-minify="1" type="text/javascript" src="http://example.org/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js"></script>
		<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js.gz',
				],
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
		],

		'minifyJssFileAndAddCdnCname' => [
			'original' => $original_html,
			'expected' => [
				'html' => <<<EXPECTED_HTML
<html>
	<head>
		<title>Sample Page</title>
		<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js"></script>
		<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js"></script>
		<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,

				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js.gz',
				],
			],

			'cdn_host' => [ '123456.rocketcdn.me' ],
			'cdn_url'  => 'https://123456.rocketcdn.me',
			'site_url' => 'http://example.org',
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
		<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js"></script>
		<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js"></script>
		<script type="text/javascript" src="https://123456.rocketcdn.me/wp-includes/js/jquery/jquery.js"></script>
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,

				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js.gz',
				],
			],

			'cdn_host' => [ '123456.rocketcdn.me' ],
			'cdn_url'  => 'https://123456.rocketcdn.me',
			'site_url' => 'http://example.org',
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
		<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js"></script>
		<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js"></script>
		<script type="text/javascript" src="https://123456.rocketcdn.me/cdnpath/wp-includes/js/jquery/jquery.js"></script>
	</head>
	<body>
	</body>
</html>
EXPECTED_HTML
				,

				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-09b5ce74889313bd51265ef983880c47.js.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-796977248f632116e0145a488743a3d2.js.gz',
				],
			],

			'cdn_host' => [ '123456.rocketcdn.me/cdnpath' ],
			'cdn_url'  => 'https://123456.rocketcdn.me/cdnpath',
			'site_url' => 'http://example.org',
		],
	],
];
