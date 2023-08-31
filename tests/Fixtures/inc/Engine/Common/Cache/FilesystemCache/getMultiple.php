<?php
return [
    'shouldReturnRightOutput' => [
        'config' => [
              'keys' => [
				  '/blog/test/file.css',
				  '/blog/test/file2.css',
			  ],
              'default' => null,
			'parsed_url' => [
				'/blog/test/file.css' =>  [
					'host' => 'example.org',
					'path' => '/blog/test/file.css',
				],
				'/blog/test/file2.css' =>  [
					'host' => 'example.org',
					'path' => '/blog/test/file2.css',
				]
			],
			'root' => '/var/html/wp-content/cache',
			'home_url' => 'http://example.org',
			'exists' => [
				'/var/html/wp-content/cache/background-css/example.org/blog/test/file.css' => true,
				'/var/html/wp-content/cache/background-css/example.org/blog/test/file2.css' => false,
			],
			'content' => [
				'/var/html/wp-content/cache/background-css/example.org/blog/test/file.css' => 'content'
			]
        ],
        'expected' => [
			'output' => [
				'/blog/test/file.css' => 'content',
				'/blog/test/file2.css' => null
			]
        ]
    ],

];
