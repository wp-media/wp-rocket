<?php
return [
    'allSuccessShouldReturnTrue' => [
		'config' => [
			'values' => [
				'http://example.org/blog/test/file.css' => 'content',
				'http://example.org/blog/test/file2.css' => 'content2',
			],
			'ttl' => null,
			'parsed_url' => [
				'http://example.org/blog/test/file.css' => [
					'host' => 'example.org',
					'path' => '/blog/test/file.css',
				],
				'http://example.org/blog/test/file2.css' => [
					'host' => 'example.org',
					'path' => '/blog/test/file2.css',
				]
			],
			'root' => '/var/html/wp-content/cache',
			'saved' => [
				'/var/html/wp-content/cache/background-css/example.org/blog/test/file.css' => [
					'content' => 'content',
					'output' => true,
				],
				'/var/html/wp-content/cache/background-css/example.org/blog/test/file2.css' => [
					'content' => 'content2',
					'output' => true,
				],
			],
		],
		'expected' => [
			'output' => true,
		]
    ],
	'oneFailShouldReturnFalse' => [
		'config' => [
			'values' => [
				'http://example.org/blog/test/file.css' => 'content',
				'http://example.org/blog/test/file2.css' => 'content2',
			],
			'ttl' => null,
			'parsed_url' => [
				'http://example.org/blog/test/file.css' => [
					'host' => 'example.org',
					'path' => '/blog/test/file.css',
				],
				'http://example.org/blog/test/file2.css' => [
					'host' => 'example.org',
					'path' => '/blog/test/file2.css',
				]
			],
			'root' => '/var/html/wp-content/cache',
			'saved' => [
				'/var/html/wp-content/cache/background-css/example.org/blog/test/file.css' => [
					'content' => 'content',
					'output' => true,
				],
				'/var/html/wp-content/cache/background-css/example.org/blog/test/file2.css' => [
					'content' => 'content2',
					'output' => false,
				],
			],
		],
		'expected' => [
			'output' => false,
		]
	],

];
