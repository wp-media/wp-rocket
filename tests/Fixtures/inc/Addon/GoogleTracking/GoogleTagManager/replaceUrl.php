<?php

return [
	'vfs_dir' => 'wp-content/cache/',
	'structure' => [
		'wp-content' => [
			'cache' => [
				'busting' => [
					'1' => []
				],
				'google-tracking' => []
			]
		]
	],
	'test_data' => [
		'shouldBailOutWhenNoUrlMatching' => [
			'config' => [
				'html' => '<html><head><script async src="https://www.test.com/rocket.js"></script></head></html>',
			],
			'expected' => '<html><head><script async src="https://www.test.com/rocket.js"></script></head></html>',
		],
		'shouldReplaceUrlWhenFullURL' => [
			'config' => [
				'url' => 'https://www.googletagmanager.com/gtag/js?id=UA-135617916-1',
				'html' => '<html><head><script async src="https://www.googletagmanager.com/gtag/js?id=UA-135617916-1"></script></head></html>',
			],
			'expected' => '<html><head><script data-no-minify="1" async src="http://example.org/wp-content/cache/busting/1/gtm-88c587e9d2fdeb7ac5d4cdd9bd8d4af5.js"></script></head></html>',
		],
		'shouldReplaceUrlWhenRelativeProtocol' => [
			'config' => [
				'url' => 'https://www.googletagmanager.com/gtag/js?id=UA-135617916-1',
				'html' => '<html><head><script async src="//www.googletagmanager.com/gtag/js?id=UA-135617916-1"></script></head></html>',
			],
			'expected' => '<html><head><script data-no-minify="1" async src="http://example.org/wp-content/cache/busting/1/gtm-88c587e9d2fdeb7ac5d4cdd9bd8d4af5.js"></script></head></html>',
		],
	]
];
