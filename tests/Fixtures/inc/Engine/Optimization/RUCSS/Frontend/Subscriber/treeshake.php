<?php

return [

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
	<style id="test3">h5{color:white;}</style >
	<noscript id="noscript2"><style id="test2">h2{color:green;}</style></noscript>
	<noscript><style id="test">div{display:none !important;}</style></noscript >
	<script type="text/javascript">
		(function($) {$("head").append("<style>.my-style{color:red;}</style>")})(jQuery);
	</script>
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
	<noscript><style id="test">div{display:none !important;}</style></noscript >
	<script type="text/javascript">
		(function($) {$("head").append("<style>.my-style{color:red;}</style>")})(jQuery);
	</script>
</head>
<body>
 content here
</body>
</html>'
		],
		'shouldNotRemoveExcludedCSSInline' => [
			'config'       => [
				'html'                  => '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>My Awesome Page</title>
	<style>h2{color:blue;}</style>
	<link rel="stylesheet" type="text/css" href="http://example.org/wp-content/themes/theme-name/style.css">
	<style id="divi-style-parent-inline-inline-css"></style>
	<style>#text-box-2136397401 {
		width: 85%;
	  }</style>
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
	<style id="divi-style-parent-inline-inline-css"></style>
	<style>#text-box-2136397401 {
		width: 85%;
	  }</style>
</head>
<body>
 content here
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
		'shouldRunRucssAndKeepSVG' => [
			'config'       => [
				'html'                  => '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>My Awesome Page</title>
	<style>#ub_styled_list li::before{top:3px;font-size:1em;height:1.1em;width:1.1em;background-image:url("data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 512 512\"><path fill=\"%238ed1fc\" d=\"M507.73 109.1c-2.24-9.03-13.54-12.09-20.12-5.51l-74.36 74.36-67.88-11.31-11.31-67.88 74.36-74.36c6.62-6.62 3.43-17.9-5.66-20.16-47.38-11.74-99.55.91-136.58 37.93-39.64 39.64-50.55 97.1-34.05 147.2L18.74 402.76c-24.99 24.99-24.99 65.51 0 90.5 24.99 24.99 65.51 24.99 90.5 0l213.21-213.21c50.12 16.71 107.47 5.68 147.37-34.22 37.07-37.07 49.7-89.32 37.91-136.73zM64 472c-13.25 0-24-10.75-24-24 0-13.26 10.75-24 24-24s24 10.74 24 24c0 13.25-10.75 24-24 24z\"></path></svg>")}
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
							'shakedCSS'      => '#ub_styled_list li::before{top:3px;font-size:1em;height:1.1em;width:1.1em;background-image:url("data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 512 512\"><path fill=\"%238ed1fc\" d=\"M507.73 109.1c-2.24-9.03-13.54-12.09-20.12-5.51l-74.36 74.36-67.88-11.31-11.31-67.88 74.36-74.36c6.62-6.62 3.43-17.9-5.66-20.16-47.38-11.74-99.55.91-136.58 37.93-39.64 39.64-50.55 97.1-34.05 147.2L18.74 402.76c-24.99 24.99-24.99 65.51 0 90.5 24.99 24.99 65.51 24.99 90.5 0l213.21-213.21c50.12 16.71 107.47 5.68 147.37-34.22 37.07-37.07 49.7-89.32 37.91-136.73zM64 472c-13.25 0-24-10.75-24-24 0-13.26 10.75-24 24-24s24 10.74 24 24c0 13.25-10.75 24-24 24z\"></path></svg>")}',
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
	<title>My Awesome Page</title><style id="wpr-usedcss">#ub_styled_list li::before{top:3px;font-size:1em;height:1.1em;width:1.1em;background-image:url("data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 512 512\"><path fill=\"%238ed1fc\" d=\"M507.73 109.1c-2.24-9.03-13.54-12.09-20.12-5.51l-74.36 74.36-67.88-11.31-11.31-67.88 74.36-74.36c6.62-6.62 3.43-17.9-5.66-20.16-47.38-11.74-99.55.91-136.58 37.93-39.64 39.64-50.55 97.1-34.05 147.2L18.74 402.76c-24.99 24.99-24.99 65.51 0 90.5 24.99 24.99 65.51 24.99 90.5 0l213.21-213.21c50.12 16.71 107.47 5.68 147.37-34.22 37.07-37.07 49.7-89.32 37.91-136.73zM64 472c-13.25 0-24-10.75-24-24 0-13.26 10.75-24 24-24s24 10.74 24 24c0 13.25-10.75 24-24 24z\"></path></svg>")}</style>
</head>
<body>
 content here
</body>
</html>'
		],
	],
];
