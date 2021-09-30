<?php
$html= '<!DOCTYPE html>
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
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
</head>
<body>
content here
</body>
</html>';
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

		'shouldNotStopWarmupProcessUpgrade' => [
			'input' => [
				'html' => $html,
				'remove_unused_css' => false,
				'upgrade_rollback' => 'upgrade',
			],
			'expected' => false,
		],

		'shouldStopWarmupUpgrade' => [
			'input' => [
				'html' => $html,
				'remove_unused_css' => true,
				'upgrade_rollback' => 'upgrade',
			],
			'expected' =>  true,
		],
		'shouldNotStopWarmupProcessRollBack' => [
			'input' => [
				'html' => $html,
				'remove_unused_css' => false,
				'upgrade_rollback' => 'rollback',
			],
			'expected' => false,
		],

		'shouldStopWarmupRollBack' => [
			'input' => [
				'html' => $html,
				'remove_unused_css' => true,
				'upgrade_rollback' => 'rollback',
			],
			'expected' =>  true,
		],

	],

];
