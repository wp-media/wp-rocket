<?php

return [

	'vfs_dir' => 'public/',

	'structure' => [

		'css' => [
			'style1.css' => '.first{color:red;}',
			'style2.css' => '.second{color:green;}',
			'style3.css' => '.third{color:#000000;}',
		],
		'scripts' => [
			'script1.js' => 'var first = "content 1";',
			'script2.js' => 'var second = "content 2";',
			'script3.js' => 'var third = "content 3";',
		],
	],

	'test_data' => [
		'shouldBailoutWithNoHTMLContent' => [
			'input' => [
				'html' => '',
			],
			'expected' => [
				'resources' => [],
			],
		],

		'shouldBailoutWithNoResourcesInHTML' => [
			'input' => [
				'html' => '<!DOCTYPE html><html><head><title></title></head><body>Content here</body></html>',
			],
			'expected' => [
				'resources' => [],
			],
		],



		'shouldQueueResources' => [
			'input' => [
				'html' => '<!DOCTYPE html><html><head><title></title>'.
				          '<link rel="stylesheet" type="text/css" href="http://example.org/css/style1.css">'.
				          '<link rel="stylesheet" type="text/css" href="http://example.org/css/style2.css">'.
				          '</head><body>Content here</body></html>',
			],
			'expected' => [
				'resources' => [
					[
						'url' => 'http://example.org/css/style1.css',
						'content' => '.first{color:red;}',
						'type' => 'css'
					],
					[
						'url' => 'http://example.org/css/style2.css',
						'content' => '.second{color:green;}',
						'type' => 'css'
					]

				],
			],
		],
	]

];
