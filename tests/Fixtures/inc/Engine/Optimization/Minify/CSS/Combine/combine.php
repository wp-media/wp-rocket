<?php

return [
	'vfs_dir' => 'wp-content/',

	'test_data' => [

		'combineCssFiles' => [
			'original' =>
				'<html><head><title>Sample Page</title>' .
				'<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
				'<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">' .
				'<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">' .
				'</head><body></body></html>',

			'expected' => [
				'html'  => '<html><head><title>Sample Page</title>' .
				           '<link rel="stylesheet" href="http://example.org/wp-content/cache/min/1/a474f99d5c9b770f1e3571c39b4ae4b6.css" media="all" data-minify="1" />' .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/a474f99d5c9b770f1e3571c39b4ae4b6.css',
					'wp-content/cache/min/1/a474f99d5c9b770f1e3571c39b4ae4b6.css.gz',
				],
				'css' => 'body{font-family:Helvetica,Arial,sans-serif;text-align:center}body{font-family:Helvetica,Arial,sans-serif;text-align:center}body{font-family:Helvetica,Arial,sans-serif;text-align:center}',
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
		],

		'dontCombineCssFilesWhenNoTitleTag' => [
			'original' =>
				'<html><head>' .
				'<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
				'<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">' .
				'<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">' .
				'</head><body></body></html>',

			'expected' => [
				'html'  => '<html><head>' .
				           '<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
				           '<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">' .
				           '<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">' .
				           '</head><body></body></html>',
				'files' => [],
				'css' => false,
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
		],

		'combineNotfoundFiles' => [
			'original' =>
				'<html><head><title>Sample Page</title>' .
				'<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/notfound.css" type="text/css" media="all">' .
				'</head><body></body></html>',

			'expected' => [
				'html'  => '<html><head><title>Sample Page</title>' .
				           '<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/notfound.css" type="text/css" media="all">' .
				           '</head><body></body></html>',
				'files' => [],
				'css' => false,
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
		],

		'combineCssFilesWithImport' => [
			'original' =>
				'<html><head><title>Sample Page</title>' .
				'<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style-import.css" type="text/css" media="all">' .
				'<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">' .
				'<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">' .
				'</head><body></body></html>',

			'expected' => [
				'html'  => '<html><head><title>Sample Page</title>' .
				           '<link rel="stylesheet" href="http://example.org/wp-content/cache/min/1/b6dcf622d68835c7b1cd01e3cb339560.css" media="all" data-minify="1" />' .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/b6dcf622d68835c7b1cd01e3cb339560.css',
					'wp-content/cache/min/1/b6dcf622d68835c7b1cd01e3cb339560.css.gz',
				],
				'css' => 'body{font-family:Helvetica,Arial,sans-serif;text-align:center}body{font-family:Helvetica,Arial,sans-serif;text-align:center}body{font-family:Helvetica,Arial,sans-serif;text-align:center}',
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
		],

		'combineCssFilesWithImportJSFile' => [
			'original' =>
				'<html><head><title>Sample Page</title>' .
				'<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style-import-jsfile.css" type="text/css" media="all">' .
				'</head><body></body></html>',

			'expected' => [
				'html'  => '<html><head><title>Sample Page</title>' .
				           '<link rel="stylesheet" href="http://example.org/wp-content/cache/min/1/c78ec42dba4ed16fe23582b1e3d03895.css" media="all" data-minify="1" />' .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/c78ec42dba4ed16fe23582b1e3d03895.css',
					'wp-content/cache/min/1/c78ec42dba4ed16fe23582b1e3d03895.css.gz',
				],
				'css' => '@import url(vfs://public/wp-content/themes/twentytwenty/assets/script.js);',
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
		],

		'combineCssFilesWithNestedImport' => [
			'original' =>
				'<html><head><title>Sample Page</title>' .
				'<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style-import2.css" type="text/css" media="all">' .
				'<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">' .
				'<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">' .
				'</head><body></body></html>',

			'expected' => [
				'html'  => '<html><head><title>Sample Page</title>' .
				           '<link rel="stylesheet" href="http://example.org/wp-content/cache/min/1/a41edef8114680bb60b530fa32be3ca5.css" media="all" data-minify="1" />' .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/a41edef8114680bb60b530fa32be3ca5.css',
					'wp-content/cache/min/1/a41edef8114680bb60b530fa32be3ca5.css.gz',
				],
				'css' => '@import "http://www.google.com/style.css";.style-import-external{color:green}.style-another-import2{color:green}.style-another-import{color:red}body{font-family:Helvetica,Arial,sans-serif;text-align:center}body{font-family:Helvetica,Arial,sans-serif;text-align:center}',
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
		],

		'combineCssFilesWithImportNotFirst' => [
			'original' =>
				'<html><head><title>Sample Page</title>' .
				'<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">' .
				'<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style-import.css" type="text/css" media="all">' .
				'<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">' .
				'</head><body></body></html>',

			'expected' => [
				'html'  => '<html><head><title>Sample Page</title>' .
				           '<link rel="stylesheet" href="http://example.org/wp-content/cache/min/1/afeb29591023f7eb6314ad594ca01138.css" media="all" data-minify="1" />' .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/afeb29591023f7eb6314ad594ca01138.css',
					'wp-content/cache/min/1/afeb29591023f7eb6314ad594ca01138.css.gz',
				],
				'css' => 'body{font-family:Helvetica,Arial,sans-serif;text-align:center}body{font-family:Helvetica,Arial,sans-serif;text-align:center}body{font-family:Helvetica,Arial,sans-serif;text-align:center}',
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
		],

		'combineCssFilesWithExternalCSS' => [
			'original' =>
				'<html><head><title>Sample Page</title>' .
				'<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
				'<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">' .
				'<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">' .
				'<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" media="all" />' .
				'</head><body></body></html>',

			'expected' => [
				'html'  => '<html><head><title>Sample Page</title>' .
				           '<link rel="stylesheet" href="http://example.org/wp-content/cache/min/1/8c71d2e9bb94d96d6f6e83744b1c1745.css" media="all" data-minify="1" />' .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/8c71d2e9bb94d96d6f6e83744b1c1745.css',
					'wp-content/cache/min/1/8c71d2e9bb94d96d6f6e83744b1c1745.css.gz',
				],
				'css' => "body{font-family:Helvetica,Arial,sans-serif;text-align:center}body{font-family:Helvetica,Arial,sans-serif;text-align:center}body{font-family:Helvetica,Arial,sans-serif;text-align:center}@font-face{font-display:swap;font-family:'FontAwesome';src:url(https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/../fonts/fontawesome-webfont.eot?v=4.7.0);src:url('https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/../fonts/fontawesome-webfont.eot?#iefix&v=4.7.0') format('embedded-opentype'),url(https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/../fonts/fontawesome-webfont.woff2?v=4.7.0) format('woff2'),url(https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/../fonts/fontawesome-webfont.woff?v=4.7.0) format('woff'),url(https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/../fonts/fontawesome-webfont.ttf?v=4.7.0) format('truetype'),url('https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/../fonts/fontawesome-webfont.svg?v=4.7.0#fontawesomeregular') format('svg');font-weight:400;font-style:normal}",
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
		],

		'combineCssFiles_andUseCdnUrl' => [
			'original' => '<html><head><title>Sample Page</title>' .
			              '<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
			              '<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css">' .
			              '<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">' .
			              '</head><body></body></html>',

			'expected' => [
				'html'  => '<html><head><title>Sample Page</title>' .
				           '<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/a474f99d5c9b770f1e3571c39b4ae4b6.css" media="all" data-minify="1" />' .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/a474f99d5c9b770f1e3571c39b4ae4b6.css',
					'wp-content/cache/min/1/a474f99d5c9b770f1e3571c39b4ae4b6.css.gz',
				],
				'css' => 'body{font-family:Helvetica,Arial,sans-serif;text-align:center}body{font-family:Helvetica,Arial,sans-serif;text-align:center}body{font-family:Helvetica,Arial,sans-serif;text-align:center}',
			],

			'cdn_host' => [ '123456.rocketcdn.me' ],
			'cdn_url'  => 'https://123456.rocketcdn.me',
			'site_url' => 'http://example.org',
		],

		'combineCssFiles_whenCdnUrl' => [
			'original' => '<html><head><title>Sample Page</title>' .
			              '<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
			              '<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/style.css">' .
			              '<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">' .
			              '</head><body></body></html>',

			'expected' => [
				'html'  => '<html><head><title>Sample Page</title>' .
				           '<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/min/1/9257670dda564cf9413d31cb2a0dc089.css" media="all" data-minify="1" />' .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/9257670dda564cf9413d31cb2a0dc089.css',
					'wp-content/cache/min/1/9257670dda564cf9413d31cb2a0dc089.css.gz',
				],
				'css' => 'body{font-family:Helvetica,Arial,sans-serif;text-align:center}body{font-family:Helvetica,Arial,sans-serif;text-align:center}body{font-family:Helvetica,Arial,sans-serif;text-align:center}',
			],

			'cdn_host' => [ '123456.rocketcdn.me' ],
			'cdn_url'  => 'https://123456.rocketcdn.me',
			'site_url' => 'http://example.org',
		],

		'combineCssFiles_whenCdnUrlWithSubdir' => [
			'original' => '<html><head><title>Sample Page</title>' .
			              '<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
			              '<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/plugins/hello-dolly/style.css">' .
			              '<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-includes/css/dashicons.min.css">' .
			              '</head><body></body></html>',

			'expected' => [
				'html'  => '<html><head><title>Sample Page</title>' .
				           '<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/a1a6011a8f78cb98deb15153d5614be7.css" media="all" data-minify="1" />' .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/a1a6011a8f78cb98deb15153d5614be7.css',
					'wp-content/cache/min/1/a1a6011a8f78cb98deb15153d5614be7.css.gz',
				],
				'css' => 'body{font-family:Helvetica,Arial,sans-serif;text-align:center}body{font-family:Helvetica,Arial,sans-serif;text-align:center}body{font-family:Helvetica,Arial,sans-serif;text-align:center}',
			],

			'cdn_host' => [ '123456.rocketcdn.me/cdnpath' ],
			'cdn_url'  => 'https://123456.rocketcdn.me/cdnpath',
			'site_url' => 'http://example.org',
		],

		'combineExcludeMediaQueriesCssFiles' => [
			'original' =>
				'<html><head><title>Sample Page</title>' .
				'<link rel="stylesheet" media="screen not (max-width: 966px)" id="font-awesome-external-css-relative-url" href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css" />' .
				'<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .

				"<link rel='stylesheet' media='screen AND (max-width: 900px)' href='http://example.org/wp-content/plugins/hello-dolly/style.css' />
				<link rel='stylesheet' media='screen AND (min-width: 500px)' href='http://example.org/wp-includes/css/dashicons.min.css' />
				<link rel='stylesheet' media='screen not (min-width: 500px)' href='http://example.org/wp-content/themes/twentytwenty/style-font-face.min.css' />
				<link rel='stylesheet' media='all AND (max-width: 900px)' href='http://example.org/wp-content/themes/twentytwenty/style-import.css' />
				<link rel='stylesheet' media='all AND (min-width: 500px)' href='http://example.org/wp-content/themes/twentytwenty/new-style.css' />
				<link rel='stylesheet' media='all not (min-width: 500px)' href='http://example.org/wp-content/themes/twentytwenty/final-style.css' />" .
				'</head><body></body></html>',

			'expected' => [
				'html'  => '<html><head><title>Sample Page</title>' .
				           '<link rel="stylesheet" href="http://example.org/wp-content/cache/min/1/dcd1a95e5d432b5d300d7c2f216d7150.css" media="all" data-minify="1" />' .
				           '<link rel="stylesheet" media="screen not (max-width: 966px)" id="font-awesome-external-css-relative-url" href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css" />' .
				           "<link rel='stylesheet' media='screen AND (max-width: 900px)' href='http://example.org/wp-content/plugins/hello-dolly/style.css' />
				<link rel='stylesheet' media='screen AND (min-width: 500px)' href='http://example.org/wp-includes/css/dashicons.min.css' />
				<link rel='stylesheet' media='screen not (min-width: 500px)' href='http://example.org/wp-content/themes/twentytwenty/style-font-face.min.css' />
				<link rel='stylesheet' media='all AND (max-width: 900px)' href='http://example.org/wp-content/themes/twentytwenty/style-import.css' />
				<link rel='stylesheet' media='all AND (min-width: 500px)' href='http://example.org/wp-content/themes/twentytwenty/new-style.css' />
				<link rel='stylesheet' media='all not (min-width: 500px)' href='http://example.org/wp-content/themes/twentytwenty/final-style.css' />" .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/dcd1a95e5d432b5d300d7c2f216d7150.css',
					'wp-content/cache/min/1/dcd1a95e5d432b5d300d7c2f216d7150.css.gz',
				],
				'css' => 'body{font-family:Helvetica,Arial,sans-serif;text-align:center}',
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
		],

		'combineIncludeMediaQueriesCssFiles' => [
			'original' =>
				'<html><head><title>Sample Page</title>' .
				'<link rel="stylesheet" media="screen , (max-width: 966px)" id="font-awesome-external-css-relative-url" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />' .
				'<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css" type="text/css" media="all">' .
				"<link rel='stylesheet' media='screen , (max-width: 900px)' href='http://example.org/wp-content/plugins/hello-dolly/style.css' />
				<link rel='stylesheet' media='all , (max-width: 900px)' href='http://example.org/wp-includes/css/dashicons.min.css' />
				<link rel='stylesheet' media='(max-width: 900px), screen' href='http://example.org/wp-content/themes/twentytwenty/style-font-face.min.css' />
				<link rel='stylesheet' media='screen' href='http://example.org/wp-content/themes/twentytwenty/new-style.css' />" .
				'</head><body></body></html>',

			'expected' => [
				'html'  => '<html><head><title>Sample Page</title>' .
				           '<link rel="stylesheet" href="http://example.org/wp-content/cache/min/1/8f6853115b8a8668f3eb52abd6d0f200.css" media="all" data-minify="1" />' .
				           '</head><body></body></html>',
				'files' => [
					'wp-content/cache/min/1/8f6853115b8a8668f3eb52abd6d0f200.css',
					'wp-content/cache/min/1/8f6853115b8a8668f3eb52abd6d0f200.css.gz',
				],
				'css' => "@font-face{font-display:swap;font-family:'FontAwesome';src:url(https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/../fonts/fontawesome-webfont.eot?v=4.7.0);src:url('https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/../fonts/fontawesome-webfont.eot?#iefix&v=4.7.0') format('embedded-opentype'),url(https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/../fonts/fontawesome-webfont.woff2?v=4.7.0) format('woff2'),url(https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/../fonts/fontawesome-webfont.woff?v=4.7.0) format('woff'),url(https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/../fonts/fontawesome-webfont.ttf?v=4.7.0) format('truetype'),url('https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/../fonts/fontawesome-webfont.svg?v=4.7.0#fontawesomeregular') format('svg');font-weight:400;font-style:normal}body{font-family:Helvetica,Arial,sans-serif;text-align:center}body{font-family:Helvetica,Arial,sans-serif;text-align:center}body{font-family:Helvetica,Arial,sans-serif;text-align:center}@font-face{font-display:swap;font-family:Helvetica}footer{color:red}",
			],

			'cdn_host' => [],
			'cdn_url'  => 'http://example.org',
			'site_url' => 'http://example.org',
		],

	],
];
