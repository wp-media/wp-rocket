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
		'shouldBailOutWhenNoOptimizeConstSet'  => [
			'config'       => [
				'no-optimize'     => true,
				'html'            => 'any html'
			],
			'api-response' => false,
			'expected'     => 'any html'
		],
		'shouldBailOutWhenRocketBypassEnabled' => [
			'config'       => [
				'bypass'          => true,
				'html'            => 'any html'
			],
			'api-response' => false,
			'expected'     => 'any html'
		],
		'shouldBailOutWhenRucssNotEnabled'     => [
			'config'       => [
				'rucss-enabled'   => false,
				'html'            => 'any html'
			],
			'api-response' => false,
			'expected'     => 'any html'
		],
		'shouldBailOutWhenUserLoggedInAndLoggedUserCacheActive'        => [
			'config'       => [
				'logged-in'       => true,
				'logged-in-cache' => true,
				'html'            => 'any html'
			],
			'api-response' => false,
			'expected'     => 'any html'
		],
//		'shouldBailOutWhenApiErrors'           => [
//			'config'       => [
//				'html'            => 'any html'
//			],
//			'api-response' => new WP_Error( 400, 'Not Available' ),
//			'expected'     => 'any html'
//		],

		// Testcase "Happy Path"
		'shouldRunRucssWhenExpected' => [
			'config'   => [
				'html'          => '<!DOCTYPE html>
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
</html>'
			],
			'api-response' => [
				'code' => 200,
			],
			'expected' => '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>My Awesome Page</title><style id="used-css">/**shaken css*/</style>
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
</html>'
		],

	],
];
