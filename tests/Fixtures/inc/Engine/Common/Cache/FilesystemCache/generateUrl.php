<?php
return [
    'existsShouldReturnValidUrl' => [
        'config' => [
              'url' => 'http://example.org/blog/test/file.css',
			  'parsed_url' => [
				  'host' => 'example.org',
				  'path' => '/blog/test/file.css',
			  ],
			'root' => '/var/html/wp-content/cache',
			'WP_CONTENT_URL' => 'http://example.org/wp-content',
			'WP_CONTENT_DIR' => '/var/html/wp-content',
			'exists' => true
        ],
        'expected' => [
			'url' => 'http://example.org/blog/test/file.css',
			'path' => '/var/html/wp-content/cache/background-css/example.org/blog/test/file.css',
			'output' => 'http://example.org/wp-content/cache/background-css/example.org/blog/test/file.css',
		]
    ],
	'notExistsShouldReturnSameUrl' => [
		'config' => [
			'url' => 'http://example.org/blog/test/file.css',
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/blog/test/file.css',
			],
			'root' => '/var/html/wp-content/cache',
			'WP_CONTENT_URL' => 'http://example.org/wp-content',
			'WP_CONTENT_DIR' => '/var/html/wp-content',
			'exists' => false
		],
		'expected' => [
			'url' => 'http://example.org/blog/test/file.css',
			'path' => '/var/html/wp-content/cache/background-css/example.org/blog/test/file.css',
			'output' => 'http://example.org/blog/test/file.css',
		]
	],

];
