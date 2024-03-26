<?php
return [
	'vfs_dir' => 'wp-content/',

	'settings' => [
		'minify_css'             => 1,
		'minify_css_key'         => 123456,
		'minify_concatenate_css' => 0,
		'cdn'                    => 0,
		'cdn_cnames'             => [],
		'cdn_zone'               => [],
	],

	'test_data' => [

		'minifyCssFiles' => [
			'original' => '<html>
				<head>
					<title>Sample Page</title>
					<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">
					<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">
					<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">
					<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style-font-face.min.css">
					<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
		</head>
				<body>
				</body>
			</html>',

			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css?ver={{mtime}}" type="text/css" media="all">
						<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css?ver={{mtime}}">
						<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">
						<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css?ver={{mtime}}">
						<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/font-awesome/4.7.0/css/font-awesome.min.css?ver={{mtime}}">
					</head>
					<body>
					<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false}</script>
					<script src=\'http://example.org/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css.gz',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css.gz',
					'wp-content/cache/min/1/font-awesome/4.7.0/css/font-awesome.min.css',
					'wp-content/cache/min/1/font-awesome/4.7.0/css/font-awesome.min.css.gz',
				],
			],

			'settings' => [
				'cdn'                    => 0,
				'cdn_cnames'             => [],
				'cdn_zone'               => [],
			],
		],

		'minifyCssFilesWithIntegrity' => [
			'original' => '<html>
				<head>
					<title>Sample Page</title>
					<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css" integrity="notvalid" type="text/css" media="all">
					<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css" integrity="notvalidalgorithm-hashed">
					<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css" integrity="sha384-notvalidhash">
					<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/fontawesome.css" integrity="sha384-kru28UjhynaepLMcLGIBjkuOAHbhva6Xuk0nZStgRk733F+oTf2JKejiH/TslLhR">
		</head>
				<body>
				</body>
			</html>',

			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/font-awesome/4.7.0/css/font-awesome.css?ver={{mtime}}" integrity="notvalid" type="text/css" media="all">
						<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css" integrity="notvalidalgorithm-hashed">
						<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css" integrity="sha384-notvalidhash">
						<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/ajax/libs/font-awesome/5.15.2/css/fontawesome.css?ver={{mtime}}">
					</head>
					<body>
					<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false}</script>
					<script src=\'http://example.org/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/font-awesome/4.7.0/css/font-awesome.css',
					'wp-content/cache/min/1/font-awesome/4.7.0/css/font-awesome.css.gz',
					'wp-content/cache/min/1/ajax/libs/font-awesome/5.15.2/css/fontawesome.css',
					'wp-content/cache/min/1/ajax/libs/font-awesome/5.15.2/css/fontawesome.css.gz',
				],
			],

			'settings' => [
				'cdn'                    => 0,
				'cdn_cnames'             => [],
				'cdn_zone'               => [],
			],
		],

		'minifyCssFilesWithRelativeUrls' => [
			'original' => '<html>
				<head>
					<title>Sample Page</title>
					<link rel="stylesheet" href="/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">
					<link rel="stylesheet" href="/wp-content/plugins/hello-dolly/style.css">
					<link rel="stylesheet" href="/wp-includes/css/dashicons.min.css">
					<link rel="stylesheet" href="/wp-content/themes/twentytwenty/style-font-face.min.css">
					<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
		</head>
				<body>
				</body>
			</html>',

			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css?ver={{mtime}}" type="text/css" media="all">
						<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css?ver={{mtime}}">
						<link rel="stylesheet" href="/wp-includes/css/dashicons.min.css">
						<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css?ver={{mtime}}">
						<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/font-awesome/4.7.0/css/font-awesome.min.css?ver={{mtime}}">
					</head>
					<body>
					<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false}</script>
					<script src=\'http://example.org/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css.gz',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css.gz',
					'wp-content/cache/min/1/font-awesome/4.7.0/css/font-awesome.min.css',
					'wp-content/cache/min/1/font-awesome/4.7.0/css/font-awesome.min.css.gz',
				],
			],

			'settings' => [
				'cdn'                    => 0,
				'cdn_cnames'             => [],
				'cdn_zone'               => [],
			],
		],

		'minifyCssFilesAndAddCDNCname' => [
			'original' => '<html>
				<head>
					<title>Sample Page</title>
					<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">
					<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">
					<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">
					<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style-font-face.min.css">
				</head>
				<body>
				</body>
			</html>',

			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css?ver={{mtime}}" type="text/css" media="all">
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css?ver={{mtime}}">
						<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css?ver={{mtime}}">

					</head>
					<body>
					<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false}</script>
					<script src=\'https://123456.rocketcdn.me/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css.gz',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css.gz',
				],
			],

			'settings' => [
				'cdn'                    => 1,
				'cdn_cnames'             => [
					'https://123456.rocketcdn.me',
				],
				'cdn_zone'               => [
					'all',
				],
			],
		],

		'minifyCssFIleWithCDNUrlAlready' => [
			'original' => '<html>
				<head>
					<title>Sample Page</title>
					<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/style.css">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/style-font-face.min.css">
				</head>
				<body>
				</body>
			</html>',

			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css?ver={{mtime}}" type="text/css" media="all">
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css?ver={{mtime}}">
						<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css?ver={{mtime}}">

					</head>
					<body>
					<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false}</script>
					<script src=\'https://123456.rocketcdn.me/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css.gz',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css.gz',
				],
			],

			'settings' => [
				'cdn'                    => 1,
				'cdn_cnames'             => [ 'https://123456.rocketcdn.me' ],
				'cdn_zone'               => [ 'all' ],
			],
		],

		'minifyCssFilesWithCDNUrlAndSubdirAlready' => [
			'original' => '<html>
				<head>
					<title>Sample Page</title>
					<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/plugins/hello-dolly/style.css">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-includes/css/dashicons.min.css">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/themes/twentytwenty/style-font-face.min.css">

				</head>
				<body>
				</body>
			</html>',

			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css?ver={{mtime}}" type="text/css" media="all">
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css?ver={{mtime}}">
						<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-includes/css/dashicons.min.css">
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css?ver={{mtime}}">

					</head>
					<body>
					<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false}</script>
					<script src=\'https://123456.rocketcdn.me/cdnpath/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style.css.gz',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min.css.gz',
				],
			],

			'settings' => [
				'cdn'                    => 1,
				'cdn_cnames'             => [ 'https://123456.rocketcdn.me/cdnpath' ],
				'cdn_zone'               => [ 'all' ],
			],
		],

	],
];
