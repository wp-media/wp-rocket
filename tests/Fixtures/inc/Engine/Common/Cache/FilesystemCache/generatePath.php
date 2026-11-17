<?php
return [
	'plainFolder' => [
		'config'   => [
			'url'             => 'http://example.org/blog/test/file.css',
			'parsed_url'      => [
				'host' => 'example.org',
				'path' => '/blog/test/file.css',
			],
			'home_parsed_url' => [
				'host' => 'example.org',
				'path' => '/',
			],
			'home_url'        => 'http://example.org',
			'root'            => '/var/html/wp-content/cache',
		],
		'expected' => [
			'url'  => 'http://example.org/blog/test/file.css',
			'path' => '/var/html/wp-content/cache/background-css/1/example.org/blog/test/file.css',
		],
	],
	'nonAsciiFolderIsDecoded' => [
		'config'   => [
			'url'             => 'http://example.org/blog/test/%C3%A9t%C3%A9/file.css',
			'parsed_url'      => [
				'host' => 'example.org',
				'path' => '/blog/test/%C3%A9t%C3%A9/file.css',
			],
			'home_parsed_url' => [
				'host' => 'example.org',
				'path' => '/',
			],
			'home_url'        => 'http://example.org',
			'root'            => '/var/html/wp-content/cache',
		],
		'expected' => [
			'url'  => 'http://example.org/blog/test/%C3%A9t%C3%A9/file.css',
			'path' => '/var/html/wp-content/cache/background-css/1/example.org/blog/test/été/file.css',
		],
	],
	'spaceFolderIsDecoded' => [
		'config'   => [
			'url'             => 'http://example.org/blog/my%20dir/file.css',
			'parsed_url'      => [
				'host' => 'example.org',
				'path' => '/blog/my%20dir/file.css',
			],
			'home_parsed_url' => [
				'host' => 'example.org',
				'path' => '/',
			],
			'home_url'        => 'http://example.org',
			'root'            => '/var/html/wp-content/cache',
		],
		'expected' => [
			'url'  => 'http://example.org/blog/my%20dir/file.css',
			'path' => '/var/html/wp-content/cache/background-css/1/example.org/blog/my dir/file.css',
		],
	],
	'traversalSequenceIsNotDecoded' => [
		'config'   => [
			'url'             => 'http://example.org/blog/%2e%2e/secret.css',
			'parsed_url'      => [
				'host' => 'example.org',
				'path' => '/blog/%2e%2e/secret.css',
			],
			'home_parsed_url' => [
				'host' => 'example.org',
				'path' => '/',
			],
			'home_url'        => 'http://example.org',
			'root'            => '/var/html/wp-content/cache',
		],
		'expected' => [
			'url'  => 'http://example.org/blog/%2e%2e/secret.css',
			'path' => '/var/html/wp-content/cache/background-css/1/example.org/blog/%2e%2e/secret.css',
		],
	],
];
