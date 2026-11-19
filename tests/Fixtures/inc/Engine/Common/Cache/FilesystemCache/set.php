<?php
return [
    'shouldSave' => [
        'config' => [
			  'rights' => 777,
              'key' => 'http://example.org/blog/test/file.css',
              'value' => 'content',
              'ttl' => null,
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/blog/test/file.css',
			],
			'root' => '/var/html/wp-content/cache',
			'home_url' => 'http://example.org',
			'saved' => true,
		],
        'expected' => [
			'content' => 'content',
			'url' => 'http://example.org/blog/test/file.css',
			'path' => '/var/html/wp-content/cache/background-css/1/example.org/blog/test/file.css',
			'output' => true,
        ]
    ],
	'failedSaveSHouldReturnFalse' => [
		'config' => [
			'rights' => 777,
			'key' => 'http://example.org/blog/test/file.css',
			'value' => 'content',
			'ttl' => null,
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/blog/test/file.css',
			],
			'root' => '/var/html/wp-content/cache',
			'home_url' => 'http://example.org',
			'saved' => false,
		],
		'expected' => [
			'content' => 'content',
			'url' => 'http://example.org/blog/test/file.css',
			'path' => '/var/html/wp-content/cache/background-css/1/example.org/blog/test/file.css',
			'output' => false,
		]
	],
	'parentSegmentsShouldSaveInHostDir' => [
		'config' => [
			'rights' => 777,
			'key' => 'http://example.org/a/../../../../x.css',
			'value' => 'content',
			'ttl' => null,
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/a/../../../../x.css',
			],
			'root' => '/var/html/wp-content/cache',
			'home_url' => 'http://example.org',
			'saved' => true,
		],
		'expected' => [
			'content' => 'content',
			'url' => 'http://example.org/a/../../../../x.css',
			'path' => '/var/html/wp-content/cache/background-css/1/example.org/x.css',
			'output' => true,
		]
	],
	'unmappableKeyShouldNotSave' => [
		'config' => [
			'rights' => 777,
			'key' => 'http://example.org/blog/test/file.php',
			'value' => 'content',
			'ttl' => null,
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/blog/test/file.php',
			],
			'root' => '/var/html/wp-content/cache',
			'home_url' => 'http://example.org',
			'saved' => true,
		],
		'expected' => [
			'content' => 'content',
			'url' => 'http://example.org/blog/test/file.php',
			'path' => null,
			'output' => false,
		]
	],
];
