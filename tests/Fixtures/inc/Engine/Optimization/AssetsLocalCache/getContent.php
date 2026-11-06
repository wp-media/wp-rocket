<?php

return [
	'vfs_dir' => 'public/',
	'structure' => [//wp-content/cache/min/3rd-party/stackpath.bootstrapcdn.com-font-awesome-4.7.0-css-font-awesome.min.css
		'wp-content' => [
			'cache' => [
				'min' => [
					'3rd-party' => [
						'stackpath.bootstrapcdn.com-font-awesome-4.7.0-css-font-awesome.min.css' => 'test_content',
					],
				],
			],
		],
	],
	'test_data' => [
		'testShouldReturnContentIfFoundLocally' => [
			'config' => [
				'url' => 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',
				'file' => 'wp-content/cache/min/3rd-party/stackpath.bootstrapcdn.com-font-awesome-4.7.0-css-font-awesome.min.css',
				'found' => true,
				'reject_html' => true,
				'file_written' => true,
			],
			'expected' => 'test_content',
		],
		'testShouldReturnContentIfNotFoundLocally' => [
			'config' => [
				'url' => 'https://example.org/fontawesome.min.css',
				'file' => 'wp-content/cache/min/3rd-party/example.org-fontawesome.min.css',
				'found' => false,
				'reject_html' => true,
				'file_written' => true,
				'response' => [
					'code' => 200,
					'headers' => [ 'content-type' => 'text/css' ],
					'body' => 'test_content',
				],
			],
			'expected' => 'test_content',
		],
		'testShouldReturnEmptyWhenResponseIs404' => [
			'config' => [
				'url' => 'https://example.org/missing.css',
				'file' => 'wp-content/cache/min/3rd-party/example.org-missing.css',
				'found' => false,
				'reject_html' => true,
				'file_written' => false,
				'response' => [
					'code' => 404,
					'headers' => [ 'content-type' => 'text/html; charset=UTF-8' ],
					'body' => '<html><style>body{display:none}</style></html>',
				],
			],
			'expected' => '',
		],
		'testShouldReturnEmptyWhenRequestFailed' => [
			'config' => [
				'url' => 'https://example.org/failed.css',
				'file' => 'wp-content/cache/min/3rd-party/example.org-failed.css',
				'found' => false,
				'reject_html' => true,
				'file_written' => false,
				'response' => [
					'code' => '',
					'headers' => [],
					'body' => '',
				],
			],
			'expected' => '',
		],
		'testShouldReturnEmptyWhenHtmlRejectedAndResponseIsHtml' => [
			'config' => [
				'url' => 'https://example.org/soft-404.css',
				'file' => 'wp-content/cache/min/3rd-party/example.org-soft-404.css',
				'found' => false,
				'reject_html' => true,
				'file_written' => false,
				'response' => [
					'code' => 200,
					'headers' => [ 'content-type' => 'text/html; charset=UTF-8' ],
					'body' => '<html><style>body{display:none}</style></html>',
				],
			],
			'expected' => '',
		],
		'testShouldReturnContentWhenHtmlAllowedAndResponseIsHtml' => [
			'config' => [
				'url' => 'https://example.org/script.js',
				'file' => 'wp-content/cache/min/3rd-party/example.org-script.js',
				'found' => false,
				'reject_html' => false,
				'file_written' => true,
				'response' => [
					'code' => 200,
					'headers' => [ 'content-type' => 'text/html; charset=UTF-8' ],
					'body' => 'var a = 1;',
				],
			],
			'expected' => 'var a = 1;',
		],
	],
];
