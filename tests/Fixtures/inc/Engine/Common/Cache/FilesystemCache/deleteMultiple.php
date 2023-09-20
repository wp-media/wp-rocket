<?php
return [
    'shouldDelete' => [
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
				'/cache/background-css/example.org/blog/test/file.css' => false,
				'/cache/background-css/example.org/blog/test' => true,
			],
			'exists' => [
				'/cache/background-css/example.org/blog/test/file.css' => true,
				'/cache/background-css/example.org/blog/test' => true,
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
				'/cache/background-css/example.org/blog/test/file.css' => false,
			],
			'exists' => [
				'/cache/background-css/example.org/blog/test/file.css' => true,
				'/cache/background-css/example.org/blog/test' => false,
			],
		],
		'expected' => [
			'output' => false
		]
	]
];
