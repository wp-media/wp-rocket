<?php
return [
    'noHostShouldAddHomeOne' => [
        'config' => [
              'url' => 'https://example.org/test',
              'home_url' => 'https://example.org',
              'home_path' => '/root/path',
              'parsed_url' => [
				  'host' => 'example.org',
				  'path' => '/test'
			  ],
              'host_url' => 'example.org',
        ],
        'expected' => [
			'url' => 'https://example.org/test',
			'home_url' => 'https://example.org',
			'output' => ''
		]
    ],
	'differentHostsShouldReturnEmpty' => [
		'config' => [
			'url' => 'https://example.org/test',
			'home_url' => 'https://example2.org',
			'home_path' => '/root/path',
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/test'
			],
			'host_url' => 'example2.org',
		],
		'expected' => [
			'url' => 'https://example.org/test',
			'home_url' => 'https://example.org',
			'output' => ''
		]
	],
	'hostShouldReturnRightPath' => [
		'config' => [
			'url' => 'https://example.org/test',
			'home_url' => 'https://example.org',
			'home_path' => '/root/path',
			'parsed_url' => [
				'host' => 'example.org',
				'path' => '/test'
			],
			'host_url' => 'example.org',
		],
		'expected' => [
			'url' => 'https://example.org/test',
			'home_url' => 'https://example.org',
			'output' => ''
		]
	],

];
