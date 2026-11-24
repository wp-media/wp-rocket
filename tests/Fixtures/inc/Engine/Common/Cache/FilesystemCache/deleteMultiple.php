<?php
return [
    'shouldDelete' => [
        'config' => [
              'keys' => [
				  '/blog/test/file.css',
				  '/blog/test/dir.css',
			  ],
			'home_url' => 'http://example.org',
			'root' => '/cache',
			'parsed_url' => [
				'/blog/test/file.css' =>  [
					'host' => 'example.org',
					'path' => '/blog/test/file.css',
				],
				'/blog/test/dir.css' =>  [
					'host' => 'example.org',
					'path' => '/blog/test/dir.css',
				],
			],
			'is_dir' => [
				'/cache/background-css/1/example.org/blog/test/file.css' => false,
				'/cache/background-css/1/example.org/blog/test/dir.css' => true,
			],
			'exists' => [
				'/cache/background-css/1/example.org/blog/test/file.css' => true,
				'/cache/background-css/1/example.org/blog/test/dir.css' => true,
			],
		],
        'expected' => [
			'output' => true
		]
    ],
	'shouldReturnFalseOnOneFail' => [
		'config' => [
			'keys' => [
				'/blog/test/file.css',
				'/blog/test/file2.css',
			],
			'home_url' => 'http://example.org',
			'root' => '/cache',
			'parsed_url' => [
				'/blog/test/file.css' =>  [
					'host' => 'example.org',
					'path' => '/blog/test/file.css',
				],
				'/blog/test/file2.css' =>  [
					'host' => 'example.org',
					'path' => '/blog/test/file2.css',
				],
			],
			'is_dir' => [
				'/cache/background-css/1/example.org/blog/test/file.css' => false,
			],
			'exists' => [
				'/cache/background-css/1/example.org/blog/test/file.css' => true,
				'/cache/background-css/1/example.org/blog/test/file2.css' => false,
			],
		],
		'expected' => [
			'output' => false
		]
	],
	'directoryKeyShouldReturnFalse' => [
		'config' => [
			'keys' => [
				'/blog/test/file.css',
				'/blog/test/',
			],
			'home_url' => 'http://example.org',
			'root' => '/cache',
			'parsed_url' => [
				'/blog/test/file.css' =>  [
					'host' => 'example.org',
					'path' => '/blog/test/file.css',
				],
				'/blog/test/' =>  [
					'host' => 'example.org',
					'path' => '/blog/test/',
				],
			],
			'is_dir' => [
				'/cache/background-css/1/example.org/blog/test/file.css' => false,
			],
			'exists' => [
				'/cache/background-css/1/example.org/blog/test/file.css' => true,
			],
		],
		'expected' => [
			'output' => false
		]
	],
];
