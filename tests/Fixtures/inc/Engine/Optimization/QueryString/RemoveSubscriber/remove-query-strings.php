<?php
return [
	'vfs_dir' => 'public/',

	'structure' => [
        'wp-includes' => [
            'js' => [
                'jquery' => [
                    'jquery.js' => 'jquery',
                ],
            ],
            'css' => [
                'dashicons.min.css' => '',
            ],
        ],
        'wp-content' => [
            'cache' => [
                'busting' => [
                    '1' => [],
                ],
            ],
            'themes' => [
                'twentytwenty' => [
                    'style.css' => 'test',
                    'assets'    => [
                        'script.js' => 'test',
                    ]
                ]
            ],
            'plugins' => [
                'hello-dolly' => [
                    'style.css'  => 'test',
                    'script.js' => 'test',
                ]
            ],
        ],
	],

	'test_data' => [
		[
			// Scripts & styles are commented in HTML comments = ignored.
			'<html>
				<head>
					<title>Page title</title>
					<!-- <link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css?ver=1.0"> -->
					<!-- <link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css?ver=3.5"> -->
					<!-- <script src="http://example.org/wp-includes/js/jquery/jquery.js?ver=5.3"></script> -->
				</head>
				<body>
				<!-- <script src="http://example.org/wp-content/plugins/hello-dolly/script.js?ver=3.5"></script> -->
				</body>
			</html>',
			'<html>
				<head>
					<title>Page title</title>
					<!-- <link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css?ver=1.0"> -->
					<!-- <link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css?ver=3.5"> -->
					<!-- <script src="http://example.org/wp-includes/js/jquery/jquery.js?ver=5.3"></script> -->
				</head>
				<body>
				<!-- <script src="http://example.org/wp-content/plugins/hello-dolly/script.js?ver=3.5"></script> -->
				</body>
			</html>',
			'settings' => [
				'cdn'        => 0,
				'cdn_cnames' => [],
				'cdn_zone'   => [],
			],
		],
		[
			// Default domain, JS & CSS files are cached busted. Files with the WordPress version in the query get the ?ver= removed.
			'<html>
				<head>
					<title>Page title</title>
					<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">
					<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css?ver=1.0">
					<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css?ver=3.5">
					<link rel="stylesheet" href="https://google.com/external.css?v=20200306">
					<script src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
					<script src="http://example.org/wp-content/themes/twentytwenty/assets/script.js?ver=1.0"></script>
				</head>
				<body>
					<script src="http://example.org/wp-content/plugins/hello-dolly/script.js?ver=3.5"></script>
					<script src="https://maps.google.com/map.js"></script>
				</body>
			</html>',
			'<html>
				<head>
					<title>Page title</title>
					<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">
					<link rel="stylesheet" href="http://example.org/wp-content/cache/busting/1/wp-content/themes/twentytwenty/style-1.0.css">
					<link rel="stylesheet" href="http://example.org/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/style-3.5.css">
					<link rel="stylesheet" href="https://google.com/external.css?v=20200306">
					<script src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
					<script src="http://example.org/wp-content/cache/busting/1/wp-content/themes/twentytwenty/assets/script-1.0.js"></script>
				</head>
				<body>
					<script src="http://example.org/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/script-3.5.js"></script>
					<script src="https://maps.google.com/map.js"></script>
				</body>
			</html>',
			'settings' => [
				'cdn'        => 0,
				'cdn_cnames' => [],
				'cdn_zone'   => [],
			],
		],
		[
			// Default domain, JS & CSS files are cached busted with CDN URL. Files with the WordPress version in the query get the ?ver= removed.
			'<html>
				<head>
					<title>Page title</title>
					<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">
					<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css?ver=1.0">
					<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css?ver=3.5">
					<link rel="stylesheet" href="https://google.com/external.css?v=20200306">
					<script src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
					<script src="http://example.org/wp-content/themes/twentytwenty/assets/script.js?ver=1.0"></script>
				</head>
				<body>
					<script src="http://example.org/wp-content/plugins/hello-dolly/script.js?ver=3.5"></script>
					<script src="https://maps.google.com/map.js"></script>
				</body>
			</html>',
			'<html>
				<head>
					<title>Page title</title>
					<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-includes/css/dashicons.min.css">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/busting/1/wp-content/themes/twentytwenty/style-1.0.css">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/style-3.5.css">
					<link rel="stylesheet" href="https://google.com/external.css?v=20200306">
					<script src="https://123456.rocketcdn.me/wp-includes/js/jquery/jquery.js"></script>
					<script src="https://123456.rocketcdn.me/wp-content/cache/busting/1/wp-content/themes/twentytwenty/assets/script-1.0.js"></script>
				</head>
				<body>
					<script src="https://123456.rocketcdn.me/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/script-3.5.js"></script>
					<script src="https://maps.google.com/map.js"></script>
				</body>
			</html>',
			'settings' => [
				'cdn'        => 1,
				'cdn_cnames' => [
					'https://123456.rocketcdn.me',
				],
				'cdn_zone'   => [
					'all',
				],
			],
		],
		[
			// Default domain, JS & CSS files are cached busted with CDN URL with subdirectory. Files with the WordPress version in the query get the ?ver= removed.
			'<html>
				<head>
					<title>Page title</title>
					<link rel="stylesheet" href="http://example.org/wp-includes/css/dashicons.min.css">
					<link rel="stylesheet" href="http://example.org/wp-content/themes/twentytwenty/style.css?ver=1.0">
					<link rel="stylesheet" href="http://example.org/wp-content/plugins/hello-dolly/style.css?ver=3.5">
					<link rel="stylesheet" href="https://google.com/external.css?v=20200306">
					<script src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
					<script src="http://example.org/wp-content/themes/twentytwenty/assets/script.js?ver=1.0"></script>
				</head>
				<body>
					<script src="http://example.org/wp-content/plugins/hello-dolly/script.js?ver=3.5"></script>
					<script src="https://maps.google.com/map.js"></script>
				</body>
			</html>',
			'<html>
				<head>
					<title>Page title</title>
					<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-includes/css/dashicons.min.css">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/busting/1/wp-content/themes/twentytwenty/style-1.0.css">
					<link rel="stylesheet" href="https://123456.rocketcdn.me/cdnpath/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/style-3.5.css">
					<link rel="stylesheet" href="https://google.com/external.css?v=20200306">
					<script src="https://123456.rocketcdn.me/cdnpath/wp-includes/js/jquery/jquery.js"></script>
					<script src="https://123456.rocketcdn.me/cdnpath/wp-content/cache/busting/1/wp-content/themes/twentytwenty/assets/script-1.0.js"></script>
				</head>
				<body>
					<script src="https://123456.rocketcdn.me/cdnpath/wp-content/cache/busting/1/wp-content/plugins/hello-dolly/script-3.5.js"></script>
					<script src="https://maps.google.com/map.js"></script>
				</body>
			</html>',
			'settings' => [
				'cdn'        => 1,
				'cdn_cnames' => [
					'https://123456.rocketcdn.me/cdnpath',
				],
				'cdn_zone'   => [
					'all',
				],
			],
		],
	],
];
