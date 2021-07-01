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
	<title>My Awesome Page</title><style id="wpr-usedcss">h1{color:red}</style>
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
	<title>My Awesome Page</title><style id="wpr-usedcss">h1{color:red}</style>
	<link rel="stylesheet" type="text/css" href="http://example.org/wp-content/themes/theme-name/style.css">
	<style>h2{color:blue;}</style>
</head>
<body>
 content here
</body>
</html>'
		],

		'shouldNotProcessItemsInsideNoscriptTag' => [
			'config'       => [
				'html'                  => '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>My Awesome Page</title>
	<style>h2{color:blue;}</style>
	<noscript id="noscript1">
		<style id="test1">h3{color:green;}</style>
		<link rel="stylesheet" type="text/css" href="http://example.org/wp-content/themes/theme-name/noscript-styles.css">
	</noscript>
	<link rel="stylesheet" type="text/css" href="http://example.org/wp-content/themes/theme-name/style.css">
	<style id="test3">h5{color:white;}</style>
	<noscript id="noscript2"><style id="test2">h2{color:green;}</style></noscript>
	<noscript><style id="test">div{display:none !important;}</style></noscript>
</head>
<body>
 content here
</body>
</html>',
				'used-css-row-contents' => [
					'url'            => 'http://example.org/home',
					'css'            => '',
					'unprocessedcss' => wp_json_encode([]),
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
							'shakedCSS'      => 'h1{color:red;}h2{color:blue;}',
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
	<title>My Awesome Page</title><style id="wpr-usedcss">h1{color:red}h2{color:blue}</style>
	<noscript id="noscript1">
		<style id="test1">h3{color:green;}</style>
		<link rel="stylesheet" type="text/css" href="http://example.org/wp-content/themes/theme-name/noscript-styles.css">
	</noscript>
	<noscript id="noscript2"><style id="test2">h2{color:green;}</style></noscript>
	<noscript><style id="test">div{display:none !important;}</style></noscript>
</head>
<body>
 content here
</body>
</html>'
		],

		'shouldNotReplaceUnprocessedCssItemsWithSpecialCharacters' => [
			'config'       => [
				'html'                  => '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>My Awesome Page</title>
	<link rel="stylesheet" type="text/css" href="http://example.org/wp-content/themes/theme-name/style.css?q=1&#038;ver=5.7.1">
	<style>h2{color:blue;}</style>
</head>
<body>
 <h1>content here</h1>
</body>
</html>',
				'used-css-row-contents' => [
					'url'            => 'http://example.org/home',
					'css'            => '',
					'unprocessedcss' => wp_json_encode(
						[
							'vfs://public/wp-content/themes/theme-name/style.css?q=1&ver=5.7.1',
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
									'content' => 'http://example.org/wp-content/themes/theme-name/style.css?q=1&ver=5.7.1',
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
	<title>My Awesome Page</title><style id="wpr-usedcss">h1{color:red}</style>
	<link rel="stylesheet" type="text/css" href="http://example.org/wp-content/themes/theme-name/style.css?q=1&#038;ver=5.7.1">
	<style>h2{color:blue;}</style>
</head>
<body>
 <h1>content here</h1>
</body>
</html>'
		],

		'shouldSendCharsetToTop' => [
			'config'       => [
				'html'                  => '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>My Awesome Page</title>
	<link rel="stylesheet" type="text/css" href="http://example.org/wp-content/themes/theme-name/style.css">
</head>
<body>
 <h1>content here</h1>
</body>
</html>',
				'used-css-row-contents' => [
					'url'            => 'http://example.org/home',
					'css'            => '',
					'unprocessedcss' => wp_json_encode( [] ),
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
							'shakedCSS'      => 'h1{color:red;}@import "anyfile404.css";@charset "UTF-8";@charset "UTF-16";@charset "UTF-32";',
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
	<title>My Awesome Page</title><style id="wpr-usedcss">@import "anyfile404.css";h1{color:red}</style>
</head>
<body>
 <h1>content here</h1>
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
				'generated-file'        => 'vfs://public/wp-content/cache/used-css/1/home/used.min.css',
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
	<link rel="stylesheet" data-no-minify="" id="wpr-usedcss-css" href="http://example.org/wp-content/cache/used-css/1/home/used.min.css?ver={{mtime}}">
	<link rel="stylesheet" type="text/css" href="http://example.org/wp-content/themes/theme-name/style.css">
	<style>h2{color:blue;}</style>
</head>
<body>
 content here
</body>
</html>'
		],

		'shouldRemovePreviouslySavedResourcesWhenInUnprocessedCssAndRetries3' => [
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
							[
								'type'    => 'link',
								'content' => 'http://example.org/wp-content/themes/theme-name/style.css',
							],
						]
					),
					'retries'        => 3,
					'is_mobile'      => false,
				],
				'has_cpcss'             => true,
				'generated-file'        => 'vfs://public/wp-content/cache/used-css/1/home/used.min.css',
				'saved-resources'       => [
					'http://example.org/wp-content/themes/theme-name/style.css',
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
	<title>My Awesome Page</title>
	<link rel="stylesheet" data-no-minify="" id="wpr-usedcss-css" href="http://example.org/wp-content/cache/used-css/1/home/used.min.css?ver={{mtime}}">
	<link rel="stylesheet" type="text/css" href="http://example.org/wp-content/themes/theme-name/style.css">
</head>
<body>
 content here
</body>
</html>'
		],
		'shouldRunRucssAndAddFontDisplaySwap' => [
			'config'       => [
				'html'                  => '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>My Awesome Page</title>
	<style>@font-face {
			font-family: "TestFont";
		}
	</style>
</head>
<body>
 content here
</body>
</html>',
				'used-css-row-contents' => [
					'url'            => 'http://example.org/home',
					'css'            => '',
					'unprocessedcss' => '',
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
							'shakedCSS'      => '@font-face{font-family:"TestFont"}',
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
	<title>My Awesome Page</title><style id="wpr-usedcss">@font-face{font-display:swap;font-family:"TestFont"}</style>
</head>
<body>
 content here
</body>
</html>'
		],
	],
];
