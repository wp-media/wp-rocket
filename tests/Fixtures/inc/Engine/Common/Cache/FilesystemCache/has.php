<?php
return [
    'existsShouldReturnTrue' => [
        'config' => [
			'key' => 'http://example.org/blog/test/file.css',
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/blog/test/file.css',
			],
			'home_url' => 'http://example.org',
			'root' => '/var/html/wp-content/cache',
			'exists' => true
		],
        'expected' => [
			'url' => 'http://example.org/blog/test/file.css',
			'path' => '/var/html/wp-content/cache/background-css/example.org/blog/test/file.css',
			'output' => true
        ]
    ],
	'existsShouldReturnFalse' => [
		'config' => [
			'key' => 'http://example.org/blog/test/file.css',
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/blog/test/file.css',
			],
			'home_url' => 'http://example.org',
			'root' => '/var/html/wp-content/cache',
			'exists' => false
		],
		'expected' => [
			'url' => 'http://example.org/blog/test/file.css',
			'path' => '/var/html/wp-content/cache/background-css/example.org/blog/test/file.css',
			'output' => false
		]
	],

];
