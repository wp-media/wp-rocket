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

		'shouldBailoutWhenDONOTROCKETOPTIMIZEEnabled' => [
			'input' => [
				'html' => 'any html',
				'DONOTROCKETOPTIMIZE' => true,
			],
			'expected' => [
				'allowed' => false,
			],
		],

		'shouldBailoutWhenBypassRocket' => [
			'input' => [
				'html' => 'any html',
				'DONOTROCKETOPTIMIZE' => false,
				'rocket_bypass' => true,
			],
			'expected' => [
				'allowed' => false,
			],
		],

		'shouldBailoutWhenOptionDisabled' => [
			'input' => [
				'html' => 'any html',
				'DONOTROCKETOPTIMIZE' => false,
				'rocket_bypass' => false,
				'remove_unused_css' => false,
			],
			'expected' => [
				'allowed' => false,
			],
		],

		'shouldBailoutWhenMetaboxOptionExcluded' => [
			'input' => [
				'html' => 'any html',
				'DONOTROCKETOPTIMIZE' => false,
				'rocket_bypass' => false,
				'remove_unused_css' => true,
				'post_metabox_option_excluded' => true,
			],
			'expected' => [
				'allowed' => false,
			],
		],

		'shouldCallResourceFetcher' => [
			'input' => [
				'html' => 'any html',
				'DONOTROCKETOPTIMIZE' => false,
				'rocket_bypass' => false,
				'remove_unused_css' => true,
			],
			'expected' => [
				'allowed' => true,
			],
		],

		'shouldCallResourceFetcherWithSomeResources' => [
			'input' => [
				'html' => '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title></title>
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
				'DONOTROCKETOPTIMIZE' => false,
				'rocket_bypass' => false,
				'remove_unused_css' => true,
			],
			'expected' => [
				'allowed' => true,
				'resources' => [
					[
						'url' => 'http://example.org/wp-content/themes/theme-name/style.css',
						'content' => '.theme-name{color:red;}',
						'type' => 'css'
					],
					[
						'url' => 'http://example.org/css/style.css',
						'content' => '.first{color:green;}',
						'type' => 'css'
					]
				]
			],
		],

	],

];
