<?php

return [

	'vfs_dir' => 'public/',

	'structure' => [

		'css' => [
			'style1.css' => '.first{color:red;}',
			'style2.css' => '.second{color:green;}',
			'style3.css' => '.third{color:#000000;}',
			'style-empty.css' => '',
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

		'shouldBailoutWithNotFoundResourcesOrEmptyContent' => [
			'input' => [
				'html' => '<!DOCTYPE html><html><head><title></title>'.
				          '<link rel="stylesheet" type="text/css" href="http://example.org/css/style-empty.css">'.
				          '<link rel="stylesheet" type="text/css" href="http://example.org/css/style-notfound.css">'.
				          '</head><body>Content here</body></html>',
			],
			'expected' => [
				'resources' => [
					[
						'url' => 'http://example.org/css/style-empty.css',
						'content' => '*',
						'type' => 'css',
						'media' => 'all'
					],
					[
						'url' => 'http://example.org/css/style-notfound.css',
						'content' => '*',
						'type' => 'css',
						'media' => 'all'
					]
				],
			],
		],

		'shouldQueueResources' => [
			'input' => [
				'html' => '<!DOCTYPE html><html><head><title></title>'.
				          '<link rel="stylesheet" type="text/css" href="http://example.org/css/style1.css?ver=123">'.
				          '<link rel="stylesheet" type="text/css" href="http://example.org/css/style2.css">'.
				          '<script type="application/ld+json" src="http://example.org/scripts/script1.js"></script>'.
				          '<script src="http://example.org/scripts/script2.js"></script>'.
				          '</head><body>Content here</body></html>',
			],
			'expected' => [
				'resources' => [
					[
						'url' => 'http://example.org/css/style1.css?ver=123',
						'content' => '.first{color:red;}',
						'type' => 'css',
						'media' => 'all'
					],
					[
						'url' => 'http://example.org/css/style2.css',
						'content' => '.second{color:green;}',
						'type' => 'css',
						'media' => 'all'
					],
					[
						'url' => 'http://example.org/scripts/script2.js',
						'content' => 'var second = "content 2";',
						'type' => 'js'
					]
				],
			],
		],

		'shouldQueueResourcesWithMedias' => [
			'input' => [
				'html' => '<!DOCTYPE html><html><head><title></title>'.
				          '<link rel="stylesheet" type="text/css" href="http://example.org/css/style1.css?ver=123" media="all">'.
				          '<link media="print" rel="stylesheet" type="text/css" href="http://example.org/css/style2.css">'.
				          '</head><body>Content here</body></html>',
			],
			'expected' => [
				'resources' => [
					[
						'url' => 'http://example.org/css/style1.css?ver=123',
						'content' => '.first{color:red;}',
						'type' => 'css',
						'media' => 'all'
					],
					[
						'url' => 'http://example.org/css/style2.css',
						'content' => '.second{color:green;}',
						'type' => 'css',
						'media' => 'print'
					]

				],
			],
		],
	]

];
