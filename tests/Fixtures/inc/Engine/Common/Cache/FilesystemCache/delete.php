<?php
return [
    'fileShouldDeleteFile' => [
        'config' => [
			'key' => '/blog/test/file.css',
			'root' => '/cache',
			'home_url' => 'http://example.org',
			'is_dir' => false,
			  'exists' => true,
			  'parsed_url' => [
				  'host' => 'example.org',
				  'path' => '/blog/test/file.css',
			  ]
		],
        'expected' => [
			'path' => '/cache/background-css/1/example.org/blog/test/file.css',
			'output' => true
		]
    ],
	'dirShouldDeleteDir' => [
		'config' => [
			'key' => '/blog/test/dir.css',
			'root' => '/cache',
			'home_url' => 'http://example.org',
			'is_dir' => true,
			'exists' => true,
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/blog/test/dir.css',
			]
		],
		'expected' => [
			'path' => '/cache/background-css/1/example.org/blog/test/dir.css',
			'output' => true
		]
	],
	'parentSegmentsShouldDeleteInHostDir' => [
		'config' => [
			'key' => '/a/../../../../outside.css',
			'root' => '/cache',
			'home_url' => 'http://example.org',
			'is_dir' => false,
			'exists' => true,
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/a/../../../../outside.css',
			]
		],
		'expected' => [
			'path' => '/cache/background-css/1/example.org/outside.css',
			'output' => true
		]
	],
	'directoryKeyShouldReturnFalse' => [
		'config' => [
			'key' => '/blog/test/',
			'root' => '/cache',
			'home_url' => 'http://example.org',
			'is_dir' => true,
			'exists' => true,
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/blog/test/',
			]
		],
		'expected' => [
			'path' => null,
			'output' => false
		]
	],
	'parentHostShouldReturnFalse' => [
		'config' => [
			'key' => 'http://../file.css',
			'root' => '/cache',
			'home_url' => 'http://example.org',
			'is_dir' => false,
			'exists' => true,
			'parsed_url' => [
				'host' => '..',
				'path' => '/file.css',
			]
		],
		'expected' => [
			'path' => null,
			'output' => false
		]
	],
	'notExistsShouldReturnFalse' => [
		'config' => [
			'key' => '/blog/test/file.css',
			'home_url' => 'http://example.org',
			'root' => '/cache',
			'is_dir' => false,
			'exists' => false,
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/blog/test/file.css',
			]
		],
		'expected' => [
			'path' => '/cache/background-css/1/example.org/blog/test/file.css',
			'output' => false
		]
	],
];
