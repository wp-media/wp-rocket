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
			],
			'expected' => 'test_content',
		],
		'testShouldReturnContentIfNotFoundLocally' => [
			'config' => [
				'url' => 'https://example.org/fontawesome.min.css',
				'file' => 'wp-content/cache/min/3rd-party/example.org-fontawesome.min.css',
				'found' => false,
			],
			'expected' => 'test_content',
		],
	],
];
