<?php
return [
    'existsShouldReturnValidUrl' => [
        'config' => [
              'url' => 'http://example.org/blog/test/file.css',
			  'parsed_url' => [
				  'host' => 'example.org',
				  'path' => '/blog/test/file.css',
			  ],
			'home_parsed_url' => [
				'host' => 'example.org',
				'path' => '/',
			],
			'home_url' => 'http://example.org',
			'root' => '/var/html/wp-content/cache',
			'WP_CONTENT_URL' => 'http://example.org/wp-content',
			'WP_CONTENT_DIR' => '/var/html/wp-content',
			'exists' => true
        ],
        'expected' => [
			'url' => 'http://example.org/blog/test/file.css',
			'path' => '/var/html/wp-content/cache/background-css/1/example.org/blog/test/file.css',
			'output' => 'http://example.org/wp-content/cache/background-css/1/example.org/blog/test/file.css',
			'home_url' => 'http://example.org',
		]
    ],
	'nonAsciiFolderShouldReturnEncodedUrl' => [
		'config' => [
			'url' => 'http://example.org/blog/test/%C3%A9t%C3%A9/file.css',
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/blog/test/%C3%A9t%C3%A9/file.css',
			],
			'home_parsed_url' => [
				'host' => 'example.org',
				'path' => '/',
			],
			'home_url' => 'http://example.org',
			'root' => '/var/html/wp-content/cache',
			'WP_CONTENT_URL' => 'http://example.org/wp-content',
			'WP_CONTENT_DIR' => '/var/html/wp-content',
			'exists' => true
		],
		'expected' => [
			'url' => 'http://example.org/blog/test/%C3%A9t%C3%A9/file.css',
			'path' => '/var/html/wp-content/cache/background-css/1/example.org/blog/test/été/file.css',
			'output' => 'http://example.org/wp-content/cache/background-css/1/example.org/blog/test/%C3%A9t%C3%A9/file.css',
			'home_url' => 'http://example.org',
		]
	],
	'spaceFolderShouldReturnEncodedUrl' => [
		'config' => [
			'url' => 'http://example.org/blog/my%20dir/file.css',
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/blog/my%20dir/file.css',
			],
			'home_parsed_url' => [
				'host' => 'example.org',
				'path' => '/',
			],
			'home_url' => 'http://example.org',
			'root' => '/var/html/wp-content/cache',
			'WP_CONTENT_URL' => 'http://example.org/wp-content',
			'WP_CONTENT_DIR' => '/var/html/wp-content',
			'exists' => true
		],
		'expected' => [
			'url' => 'http://example.org/blog/my%20dir/file.css',
			'path' => '/var/html/wp-content/cache/background-css/1/example.org/blog/my dir/file.css',
			'output' => 'http://example.org/wp-content/cache/background-css/1/example.org/blog/my%20dir/file.css',
			'home_url' => 'http://example.org',
		]
	],
	'noDomainShouldReturnValidUrl' => [
		'config' => [
			'url' => '/blog/test/file.css',
			'parsed_url' => [
				'host' => null,
				'path' => '/blog/test/file.css',
			],
			'home_parsed_url' => [
				'host' => 'example.org',
				'path' => '/',
			],
			'home_url' => 'http://example.org',
			'root' => '/var/html/wp-content/cache',
			'WP_CONTENT_URL' => 'http://example.org/wp-content',
			'WP_CONTENT_DIR' => '/var/html/wp-content',
			'exists' => true
		],
		'expected' => [
			'url' => '/blog/test/file.css',
			'path' => '/var/html/wp-content/cache/background-css/1/example.org/blog/test/file.css',
			'output' => 'http://example.org/wp-content/cache/background-css/1/example.org/blog/test/file.css',
			'home_url' => 'http://example.org',
		]
	],
	'notExistsShouldReturnCacheUrl' => [
		'config' => [
			'url' => 'http://example.org/blog/test/file.css',
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/blog/test/file.css',
			],
			'home_parsed_url' => [
				'host' => 'example.org',
				'path' => '/',
			],
			'home_url' => 'http://example.org',
			'root' => '/var/html/wp-content/cache',
			'WP_CONTENT_URL' => 'http://example.org/wp-content',
			'WP_CONTENT_DIR' => '/var/html/wp-content',
			'exists' => false
		],
		'expected' => [
			'url' => 'http://example.org/blog/test/file.css',
			'path' => '/var/html/wp-content/cache/background-css/1/example.org/blog/test/file.css',
			'output' => 'http://example.org/wp-content/cache/background-css/1/example.org/blog/test/file.css',
			'home_url' => 'http://example.org',
		]
	],

];
