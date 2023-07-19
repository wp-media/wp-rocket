<?php
return [
    'fileShouldDeleteFile' => [
        'config' => [
              'key' => '/blog/test/file.css',
			  'root' => '/cache',
			  'is_dir' => false,
			  'exists' => true,
			  'parsed_url' => [
				  'host' => 'example.org',
				  'path' => '/blog/test/file.css',
			  ]
		],
        'expected' => [
			'path' => '/cache/background-css/example.org/blog/test/file.css',
			'output' => true
		]
    ],
	'dirShouldDeleteDir' => [
		'config' => [
			'key' => '/blog/test/',
			'root' => '/cache',
			'is_dir' => true,
			'exists' => true,
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/blog/test/',
			]
		],
		'expected' => [
			'path' => '/cache/background-css/example.org/blog/test',
			'output' => true
		]
	],
	'notExistsShouldReturnFalse' => [
		'config' => [
			'key' => '/blog/test/file.css',
			'root' => '/cache',
			'is_dir' => false,
			'exists' => false,
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/blog/test/file.css',
			]
		],
		'expected' => [
			'path' => '/cache/background-css/example.org/blog/test/file.css',
			'output' => false
		]
	],
];
