<?php
return [
	'vfs_dir'   => 'wordpress/',

	'structure' => [
		'wordpress' => [
			'wp-includes' => [
				'js' => [
					'jquery' => [
						'jquery.js' => 'jquery',
					],
				],
				'css' => [
					'dashicons.min.css' => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
				],
			],
			'wp-content' => [
				'cache' => [
					'min' => [
						'1' => [],
					],
				],
				'themes' => [
					'twentytwenty' => [
						'style.css' => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
						'assets'    => [
							'script.js' => 'test',
						]
					]
				],
				'plugins' => [
					'hello-dolly' => [
						'style.css'  => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
						'script.js' => 'test',
					]
				],
			],
		],
	],

	'test_data' => [
		// Minify JS files
		[
			// Test Data: Original JS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
				</head>
				<body>
				</body>
			</html>',
			// Expected: Minified JS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<script data-minify="1" type="text/javascript" src="http://example.org/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-cdcfd4a96e52edbc4d3e7d5e887dbd11.js"></script>
					<script data-minify="1" type="text/javascript" src="http://example.org/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-82c5174e342f25861cb20cab85ecb625.js"></script>
					<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
				</head>
				<body>
				</body>
			</html>',
			'settings' => [
				'minify_concatenate_js' => 0,
				'cdn'                   => 0,
				'cdn_cnames'            => [],
				'cdn_zone'              => [],
			],
		],
		// Minify JS files to CDN URL
		[
			// Test Data: Original JS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
				</head>
				<body>
				</body>
			</html>',
			// Expected: Minified JS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-cdcfd4a96e52edbc4d3e7d5e887dbd11.js"></script>
					<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-82c5174e342f25861cb20cab85ecb625.js"></script>
					<script type="text/javascript" src="https://123456.rocketcdn.me/wp-includes/js/jquery/jquery.js"></script>
				</head>
				<body>
				</body>
            </html>',
            'settings' => [
				'minify_concatenate_js' => 0,
				'cdn'                   => 1,
				'cdn_cnames'            => [
                    'https://123456.rocketcdn.me',
                ],
				'cdn_zone'              => [
                    'all',
                ],
			],
		],
		// Minify JS files with CDN URL.
		[
			// Test Data: Original JS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="https://123456.rocketcdn.me/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="https://123456.rocketcdn.me/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="https://123456.rocketcdn.me/wp-includes/js/jquery/jquery.js"></script>
				</head>
				<body>
				</body>
			</html>',
			// Expected: Minified JS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-cdcfd4a96e52edbc4d3e7d5e887dbd11.js"></script>
					<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-82c5174e342f25861cb20cab85ecb625.js"></script>
					<script type="text/javascript" src="https://123456.rocketcdn.me/wp-includes/js/jquery/jquery.js"></script>
				</head>
				<body>
				</body>
			</html>',
			'settings' => [
				'minify_concatenate_js' => 0,
				'cdn'                   => 1,
				'cdn_cnames'            => [
                    'https://123456.rocketcdn.me',
                ],
				'cdn_zone'              => [
                    'all',
                ],
			],
        ],
        // Minify JS files with CDN URL.
		[
			// Test Data: Original JS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<script type="text/javascript" src="http://example.org/wp-content/themes/twentytwenty/assets/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-content/plugins/hello-dolly/script.js"></script>
					<script type="text/javascript" src="http://example.org/wp-includes/js/jquery/jquery.js"></script>
				</head>
				<body>
				</body>
			</html>',
			// Expected: Minified JS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/themes/twentytwenty/assets/script-cdcfd4a96e52edbc4d3e7d5e887dbd11.js"></script>
					<script data-minify="1" type="text/javascript" src="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/wp-content/plugins/hello-dolly/script-82c5174e342f25861cb20cab85ecb625.js"></script>
					<script type="text/javascript" src="https://123456.rocketcdn.me/cdnpath/wp-includes/js/jquery/jquery.js"></script>
				</head>
				<body>
				</body>
			</html>',
			'settings' => [
				'minify_concatenate_js' => 0,
				'cdn'                   => 1,
				'cdn_cnames'            => [
                    'https://123456.rocketcdn.me/cdnpath',
                ],
				'cdn_zone'              => [
                    'all',
                ],
			],
        ],
        // Combine JS files
		[
			// Test Data: Original JS files.
			'<html>
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
			</html>',
			// Expected: Combined JS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<script>
					nonce = "nonce";
					</script>
				</head>
				<body>
					<script src="http://example.org/wp-content/cache/min/1/f819bcaed244d53d3b4ffc4c5cc0efdc.js" data-minify="1"></script>
				</body>
			</html>',
			'settings' => [
				'minify_concatenate_js' => 1,
				'cdn'                   => 0,
				'cdn_cnames'            => [],
				'cdn_zone'              => [],
			],
		],
		// Combine JS files to CDN URL
		[
			// Test Data: Original JS files.
			'<html>
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
			</html>',
			// Expected: Combined JS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<script>
					nonce = "nonce";
					</script>
				</head>
				<body>
					<script src="https://123456.rocketcdn.me/wp-content/cache/min/1/f819bcaed244d53d3b4ffc4c5cc0efdc.js" data-minify="1"></script>
				</body>
			</html>',
			'settings' => [
				'minify_concatenate_js' => 1,
				'cdn'                   => 1,
				'cdn_cnames'            => [
                    'https://123456.rocketcdn.me',
                ],
				'cdn_zone'              => [
                    'all',
                ],
			],
		],
		// Combine JS files with CDN URL
		[
			// Test Data: Original JS files.
			'<html>
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
			</html>',
			// Expected: Combined JS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<script>
					nonce = "nonce";
					</script>
				</head>
				<body>
					<script src="https://123456.rocketcdn.me/wp-content/cache/min/1/f819bcaed244d53d3b4ffc4c5cc0efdc.js" data-minify="1"></script>
				</body>
			</html>',
			'settings' => [
				'minify_concatenate_js' => 1,
				'cdn'                   => 1,
				'cdn_cnames'            => [
                    'https://123456.rocketcdn.me',
                ],
				'cdn_zone'              => [
                    'all',
                ],
			],
		],
		// Combine JS files with CDN URL with subdirectory
		[
			// Test Data: Original JS files.
			'<html>
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
			</html>',
			// Expected: Combined JS files.
			'<html>
				<head>
					<title>Sample Page</title>
					<script>
					nonce = "nonce";
					</script>
				</head>
				<body>
					<script src="https://123456.rocketcdn.me/cdnpath/wp-content/cache/min/1/f819bcaed244d53d3b4ffc4c5cc0efdc.js" data-minify="1"></script>
				</body>
			</html>',
			'settings' => [
				'minify_concatenate_js' => 1,
				'cdn'                   => 1,
				'cdn_cnames'            => [
                    'https://123456.rocketcdn.me/cdnpath',
                ],
				'cdn_zone'              => [
                    'all',
                ],
			],
		],
	],
];
