<?php

return [
	'vfs_dir' => 'public/',

	'structure' => [
		'wp-content' => [
			'themes' => [
				'theme-name' => [
					'style.css' => '.theme-name{color:red;}'
				]
			]
		],

		'css' => [
			'style.css' => '.first{color:red;}',
		],
	],

	'test_data' => [
		// Testcases for Bailout/Short-circuit
		'shouldBailOutWhenNoOptimizeConstSet'                   => [
			'config'       => [
				'no-optimize' => true,
				'html'        => 'any html'
			],
			'api-response' => false,
			'expected'     => 'any html'
		],
		'shouldBailOutWhenRocketBypassEnabled'                  => [
			'config'       => [
				'bypass' => true,
				'html'   => 'any html'
			],
			'api-response' => false,
			'expected'     => 'any html'
		],
		'shouldBailOutWhenRucssNotEnabled'                      => [
			'config'       => [
				'rucss-enabled' => false,
				'html'          => 'any html'
			],
			'api-response' => false,
			'expected'     => 'any html'
		],
		'shouldBailOutWhenUserLoggedInAndLoggedUserCacheActive' => [
			'config'       => [
				'logged-in'       => true,
				'logged-in-cache' => true,
				'html'            => 'any html'
			],
			'api-response' => false,
			'expected'     => 'any html'
		],
		'shouldBailOutWhenApiErrors'                            => [
			'config'       => [
				'html' => 'any html'
			],
			'api-response' => new WP_Error( 400, 'Not Available' ),
			'expected'     => 'any html'
		],

		'shouldRunRucssWithoutRetriesWhenRetriesAre3' => [
			'config'       => [
				'html'                  => '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>My Awesome Page</title>
	<link rel="stylesheet" type="text/css" href="http://example.org/wp-content/themes/theme-name/style.css">
	<link rel="stylesheet" type="text/css" href="//example.org/wp-content/themes/theme-name/style.css">
	<link rel="stylesheet" type="text/css" href="/wp-content/themes/theme-name/style.css">
	<link rel="stylesheet" type="text/css" href="wp-content/themes/theme-name/style.css">
	<link rel="stylesheet" type="text/css" href="/css/style.css">
	<link rel="stylesheet" type="text/css" href="http://external.com/css/style.css">
	<link rel="stylesheet" type="text/css" href="//external.com/css/style.css">
</head>
<body>
 content here
</body>
</html>',
				'used-css-row-contents' => [
					'url'            => 'http://example.org/home',
					'css'            => 'h1{color:red;}',
					'unprocessedcss' => wp_json_encode( [] ),
					'retries'        => 3,
					'is_mobile'      => false,
				],

			],
			'api-response' => [
				'code' => 200,
			],
			'expected'     => '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>My Awesome Page</title><style id="wpr-usedcss">h1{color:red;}</style>
</head>
<body>
 content here
</body>
</html>'
		],

		'shouldRunRucssWithRetriesWhenRetriesAreUnder3' => [
			'config'       => [
				'html'                  => '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>My Awesome Page</title>
	<link rel="stylesheet" type="text/css" href="http://example.org/wp-content/themes/theme-name/style.css">
	<link rel="stylesheet" type="text/css" href="//example.org/wp-content/themes/theme-name/style.css">
	<link rel="stylesheet" type="text/css" href="/wp-content/themes/theme-name/style.css">
	<link rel="stylesheet" type="text/css" href="wp-content/themes/theme-name/style.css">
	<link rel="stylesheet" type="text/css" href="/css/style.css">
	<link rel="stylesheet" type="text/css" href="http://external.com/css/style.css">
	<link rel="stylesheet" type="text/css" href="//external.com/css/style.css">
</head>
<body>
 content here
</body>
</html>',
				'used-css-row-contents' => [
					'url'            => 'http://example.org/home',
					'css'            => '',
					'unprocessedcss' => wp_json_encode(
						[
							'http://example.org/wp-content/themes/theme-name/style.css',
						]
					),
					'retries'        => 1,
					'is_mobile'      => false,
				],

			],
			'api-response' => [
				'body'     => json_encode(
					[
						'code'     => 200,
						'message'  => 'OK',
						'contents' => [
							'shakedCSS'      => 'h1{color:red;}',
							'unProcessedCss' => [],
						],
					]
				),
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
			],
			'expected'     => '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>My Awesome Page</title><style id="wpr-usedcss">h1{color:red;}</style>
</head>
<body>
 content here
</body>
</html>'
		],

		'shouldNotReplaceUnprocessedCssItems' => [
			'config'       => [
				'html'                  => '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>My Awesome Page</title>
	<link rel="stylesheet" type="text/css" href="http://example.org/wp-content/themes/theme-name/style.css">
	<link rel="stylesheet" type="text/css" href="//example.org/wp-content/themes/theme-name/style.css">
	<link rel="stylesheet" type="text/css" href="/wp-content/themes/theme-name/style.css">
	<link rel="stylesheet" type="text/css" href="wp-content/themes/theme-name/style.css">
	<link rel="stylesheet" type="text/css" href="/css/style.css">
	<link rel="stylesheet" type="text/css" href="http://external.com/css/style.css">
	<link rel="stylesheet" type="text/css" href="//external.com/css/style.css">
	<style>h2{color:blue;}</style>
</head>
<body>
 content here
</body>
</html>',
				'used-css-row-contents' => [
					'url'            => 'http://example.org/home',
					'css'            => '',
					'unprocessedcss' => wp_json_encode(
						[
							'vfs://public/wp-content/themes/theme-name/style.css',
						]
					),
					'retries'        => 1,
					'is_mobile'      => false,
				],

			],
			'api-response' => [
				'body'     => json_encode(
					[
						'code'     => 200,
						'message'  => 'OK',
						'contents' => [
							'shakedCSS'      => 'h1{color:red;}',
							'unProcessedCss' => [
								[
									'type'    => 'link',
									'content' => 'http://example.org/wp-content/themes/theme-name/style.css',
								],
								[
									'type'    => 'inline',
									'content' => 'h2{color:blue;}',
								],
							],
						],
					]
				),
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
			],
			'expected'     => '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>My Awesome Page</title><style id="wpr-usedcss">h1{color:red;}</style>
	<link rel="stylesheet" type="text/css" href="http://example.org/wp-content/themes/theme-name/style.css">
	<style>h2{color:blue;}</style>
</head>
<body>
 content here
</body>
</html>'
		],

		'shouldNotInterfereWithCPCSS' => [
			'config'       => [
				'html'                  => '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>My Awesome Page</title>
	<link rel="stylesheet" type="text/css" href="http://example.org/wp-content/themes/theme-name/style.css">
	<link rel="stylesheet" type="text/css" href="//example.org/wp-content/themes/theme-name/style.css">
	<link rel="stylesheet" type="text/css" href="/wp-content/themes/theme-name/style.css">
	<link rel="stylesheet" type="text/css" href="wp-content/themes/theme-name/style.css">
	<link rel="stylesheet" type="text/css" href="/css/style.css">
	<link rel="stylesheet" type="text/css" href="http://external.com/css/style.css">
	<link rel="stylesheet" type="text/css" href="//external.com/css/style.css">
	<style>h2{color:blue;}</style>
</head>
<body>
 content here
</body>
</html>',
				'used-css-row-contents' => [
					'url'            => 'http://example.org/home',
					'css'            => '',
					'unprocessedcss' => wp_json_encode(
						[
							'http://example.org/wp-content/themes/theme-name/style.css',
						]
					),
					'retries'        => 1,
					'is_mobile'      => false,
				],
				'has_cpcss'             => true,
				'generated-file' => 'vfs://public/wp-content/cache/used-css/2664e301f9920094b0c21e1378f8702a.css',
			],
			'api-response' => [
				'body'     => json_encode(
					[
						'code'     => 200,
						'message'  => 'OK',
						'contents' => [
							'shakedCSS'      => 'h1{color:red;}',
							'unProcessedCss' => [
								[
									'type'    => 'link',
									'content' => 'http://example.org/wp-content/themes/theme-name/style.css',
								],
								[
									'type'    => 'inline',
									'content' => 'h2{color:blue;}',
								],
							],
						],
					]
				),
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
			],
			'expected'     => '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>My Awesome Page</title>
	<link rel="stylesheet" id="wpr-usedcss-css" href="http://example.org/wp-content/cache/used-css/2664e301f9920094b0c21e1378f8702a.css?ver={{mtime}}">
	<link rel="stylesheet" type="text/css" href="http://example.org/wp-content/themes/theme-name/style.css">
	<style>h2{color:blue;}</style>
</head>
<body>
 content here
</body>
</html>'
		],
	],

];
