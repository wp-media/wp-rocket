<?php
return [
    'existsShouldReturnContent' => [
        'config' => [
              'key' => 'http://example.org/blog/test/file.css',
              'default' => null,
			  'parsed_url' => [
					'host' => 'example.org',
					'path' => '/blog/test/file.css',
			  ],
			  'root' => '/var/html/wp-content/cache',
			'home_url' => 'http://example.org',
			'exists' => true,
			  'content' => 'content'
		],
        'expected' => [
			'url' => 'http://example.org/blog/test/file.css',
			'path' => '/var/html/wp-content/cache/background-css/example.org/blog/test/file.css',
			'output' => 'content'
        ]
    ],
	'noExistShouldReturnDefault' => [
		'config' => [
			'key' => 'http://example.org/blog/test/file.css',
			'default' => null,
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/blog/test/file.css',
			],
			'root' => '/var/html/wp-content/cache',
			'home_url' => 'http://example.org',
			'exists' => false,
			'content' => 'content'
		],
		'expected' => [
			'url' => 'http://example.org/blog/test/file.css',
			'path' => '/var/html/wp-content/cache/background-css/example.org/blog/test/file.css',
			'output' => null
		]
	]
];
