<?php
return [
    'shouldSave' => [
        'config' => [
              'key' => 'http://example.org/blog/test/file.css',
              'value' => 'content',
              'ttl' => null,
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/blog/test/file.css',
			],
			'root' => '/var/html/wp-content/cache',
			'saved' => true,
		],
        'expected' => [
			'content' => 'content',
			'url' => 'http://example.org/blog/test/file.css',
			'path' => '/var/html/wp-content/cache/background-css/example.org/blog/test/file.css',
			'output' => true,
        ]
    ],
	'failedSaveSHouldReturnFalse' => [
		'config' => [
			'key' => 'http://example.org/blog/test/file.css',
			'value' => 'content',
			'ttl' => null,
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/blog/test/file.css',
			],
			'root' => '/var/html/wp-content/cache',
			'saved' => false,
		],
		'expected' => [
			'content' => 'content',
			'url' => 'http://example.org/blog/test/file.css',
			'path' => '/var/html/wp-content/cache/background-css/example.org/blog/test/file.css',
			'output' => false,
		]
	],

];
