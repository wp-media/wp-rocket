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
		'shouldBailOutWhenNoValidScript' => [
			'config' => [
				'html' => '<html><head><script async src="https://www.test.com/rocket.js"></script></head></html>',
			],
			'expected' => '<html><head><script async src="https://www.test.com/rocket.js"></script></head></html>'
		],
		'shouldReplaceUrlWithFullUrl' => [
			'config' => [
				'url' => 'https://www.google-analytics.com/analytics.js',
				'html' => '<html><head><script async src="https://www.google-analytics.com/analytics.js"></script></head></html>',
			],
			'expected' => '<html><head><script async src="{HOME_URL}/wp-content/cache/busting/google-tracking/ga-88c587e9d2fdeb7ac5d4cdd9bd8d4af5.js"></script></head></html>'
		],
		'shouldReplaceUrlWithRelativeUrl' => [
			'config' => [
				'url' => 'https://www.google-analytics.com/analytics.js',
				'html' => '<html><head><script async src="//www.google-analytics.com/analytics.js"></script></head></html>',
			],
			'expected' => '<html><head><script async src="{HOME_URL}/wp-content/cache/busting/google-tracking/ga-88c587e9d2fdeb7ac5d4cdd9bd8d4af5.js"></script></head></html>'
		]
	]
];
