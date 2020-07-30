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
				</head>
				<body>
				</body>
			</html>',

			'expected' => [
				'html'  => '<html>
					<head>
						<title>Sample Page</title>
						<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css" type="text/css" media="all">
						<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css">
						<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">
						<link data-minify="1" rel="stylesheet" href="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-810497c120aacb0db0d64737badecd9c.css">
					</head>
					<body>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css.gz',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-810497c120aacb0db0d64737badecd9c.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-810497c120aacb0db0d64737badecd9c.css.gz',
				],
			],

			'settings' => [
				'minify_concatenate_css' => 0,
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
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css" type="text/css" media="all">
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css">
						<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-810497c120aacb0db0d64737badecd9c.css">

					</head>
					<body>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css.gz',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-810497c120aacb0db0d64737badecd9c.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-810497c120aacb0db0d64737badecd9c.css.gz',
				],
			],

			'settings' => [
				'minify_concatenate_css' => 0,
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
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css" type="text/css" media="all">
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css">
						<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-810497c120aacb0db0d64737badecd9c.css">

					</head>
					<body>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css.gz',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-810497c120aacb0db0d64737badecd9c.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-810497c120aacb0db0d64737badecd9c.css.gz',
				],
			],

			'settings' => [
				'minify_concatenate_css' => 0,
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
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css" type="text/css" media="all">
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css">
						<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-includes/css/dashicons.min.css">
						<link data-minify="1" rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-810497c120aacb0db0d64737badecd9c.css">

					</head>
					<body>
					</body>
				</html>',
				'files' => [
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-5360e3be2666897518a1821fbecc9d28.css.gz',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css',
					'wp-content/cache/min/1/wp-content/plugins/hello-dolly/style-4a16b4cd55f600cc39947847baa15308.css.gz',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-810497c120aacb0db0d64737badecd9c.css',
					'wp-content/cache/min/1/wp-content/themes/twentytwenty/style-font-face.min-810497c120aacb0db0d64737badecd9c.css.gz',
				],
			],

			'settings' => [
				'minify_concatenate_css' => 0,
				'cdn'                    => 1,
				'cdn_cnames'             => [ 'https://123456.rocketcdn.me/cdnpath' ],
				'cdn_zone'               => [ 'all' ],
			],
		],

		'combineCssFiles' => [
			'original' => '<html>' .
			              '<head>' .
			              '<title>Sample Page</title>' .
			              '<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
			              '<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">' .
						  '<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">' .
			              '</head>' .
			              '<body>' .
			              '</body>' .
			              '</html>',

			'expected' => [
				'html'  => '<html>' .
				           '<head>' .
				           '<title>Sample Page</title>' .
				           '<link rel="stylesheet" href="http://example.org/wp-content/cache/min/1/eb7ac62f1625bbbcc73a159d5f6aa290.css" media="all" data-minify="1" />' .
				           '</head>' .
				           '<body>' .
				           '</body>' .
				           '</html>',
				'files' => [
					'wp-content/cache/min/1/eb7ac62f1625bbbcc73a159d5f6aa290.css',
					'wp-content/cache/min/1/eb7ac62f1625bbbcc73a159d5f6aa290.css.gz',
				],
			],

			'settings' => [
				'minify_concatenate_css' => 1,
				'cdn'                    => 0,
				'cdn_cnames'             => [],
				'cdn_zone'               => [],
			],
		],

		'combineCssFilesWithExternalCSS' => [
			'original' => '<html>' .
			              '<head>' .
			              '<title>Sample Page</title>' .
			              '<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
			              '<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">' .
						  '<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">' .
						  '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" media="all" />' .
			              '</head>' .
			              '<body>' .
			              '</body>' .
			              '</html>',

			'expected' => [
				'html'  => '<html>' .
				           '<head>' .
				           '<title>Sample Page</title>' .
				           '<link rel="stylesheet" href="http://example.org/wp-content/cache/min/1/245ce39ab456beba0af083ce5b883e9d.css" media="all" data-minify="1" />' .
				           '</head>' .
				           '<body>' .
				           '</body>' .
				           '</html>',
				'files' => [
					'wp-content/cache/min/1/245ce39ab456beba0af083ce5b883e9d.css',
					'wp-content/cache/min/1/245ce39ab456beba0af083ce5b883e9d.css.gz',
				],
			],

			'settings' => [
				'minify_concatenate_css' => 1,
				'cdn'                    => 0,
				'cdn_cnames'             => [],
				'cdn_zone'               => [],
			],
		],

		'combineCssFilesWithCDNUrl1' => [
			'original' => '<html>' .
			              '<head>' .
			              '<title>Sample Page</title>' .
			              '<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
			              '<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">' .
			              '<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">' .
			              '</head>' .
			              '<body>' .
			              '</body>' .
			              '</html>',

			'expected' => [
				'html'  => '<html>' .
				           '<head>' .
				           '<title>Sample Page</title>' .
				           '<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/eb7ac62f1625bbbcc73a159d5f6aa290.css" media="all" data-minify="1" />' .
				           '</head>' .
				           '<body>' .
				           '</body>' .
				           '</html>',
				'files' => [
					'wp-content/cache/min/1/eb7ac62f1625bbbcc73a159d5f6aa290.css',
					'wp-content/cache/min/1/eb7ac62f1625bbbcc73a159d5f6aa290.css.gz',
				],
			],

			'settings' => [
				'minify_concatenate_css' => 1,
				'cdn'                    => 1,
				'cdn_cnames'             => [ 'https://123456.rocketcdn.me' ],
				'cdn_zone'               => [ 'all' ],
			],
		],

		'combineCssFilesWithCDNUrl2' => [
			'original' => '<html>' .
			              '<head>' .
			              '<title>Sample Page</title>' .
			              '<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
			              '<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/style.css">' .
			              '<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">' .
			              '</head>' .
			              '<body>' .
			              '</body>' .
			              '</html>',

			'expected' => [
				'html'  => '<html>' .
				           '<head>' .
				           '<title>Sample Page</title>' .
				           '<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/6b11b532cd22becbe49e0ad275f13fd5.css" media="all" data-minify="1" />' .
				           '</head>' .
				           '<body>' .
				           '</body>' .
				           '</html>',
				'files' => [
					'wp-content/cache/min/1/6b11b532cd22becbe49e0ad275f13fd5.css',
					'wp-content/cache/min/1/6b11b532cd22becbe49e0ad275f13fd5.css.gz',
				],
			],

			'settings' => [
				'minify_concatenate_css' => 1,
				'cdn'                    => 1,
				'cdn_cnames'             => [ 'https://123456.rocketcdn.me' ],
				'cdn_zone'               => [ 'all' ],
			],
		],

		'combineCssFIlesWithCDNUrlWithSubdir' => [
			'original' => '<html>' .
			              '<head>' .
			              '<title>Sample Page</title>' .
			              '<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
			              '<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/plugins/hello-dolly/style.css">' .
			              '<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-includes/css/dashicons.min.css">' .
			              '</head>' .
			              '<body>' .
			              '</body>' .
			              '</html>',

			'expected' => [
				'html'  => '<html>' .
				           '<head>' .
				           '<title>Sample Page</title>' .
				           '<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/35e2692ad46a43976a37c3d91a650f28.css" media="all" data-minify="1" />' .
				           '</head>' .
				           '<body>' .
				           '</body>' .
				           '</html>',
				'files' => [
					'wp-content/cache/min/1/35e2692ad46a43976a37c3d91a650f28.css',
					'wp-content/cache/min/1/35e2692ad46a43976a37c3d91a650f28.css.gz',
				],
			],

			'settings' => [
				'minify_concatenate_css' => 1,
				'cdn'                    => 1,
				'cdn_cnames'             => [
					'https://123456.rocketcdn.me/cdnpath',
				],
				'cdn_zone'               => [
					'all',
				],
			],
		],
	],
];
