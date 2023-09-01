<?php
return [
    'PathShouldUseFilesystem' => [
        'config' => [
              'path' => '/my/file',
			  'is_url' => false,
			  'content' => 'content',
			  'destination' => '/path/new',
			  'code' => 200
        ],
        'expected' => 'content'
    ],
	'UrlShouldUseRemoteGet' => [
		'config' => [
			'path' => 'http://example.org/test',
			'is_url' => true,
			'content' => 'content',
			'response' => 'response',
			'body' => 'body',
			'destination' => '/path/new',
			'code' => 200
		],
		'expected' => 'body'
	],
	'UrlContentNUllShouldReturnFalse' => [
		'config' => [
			'path' => 'http://example.org/test',
			'is_url' => true,
			'content' => 'content',
			'response' => 'response',
			'body' => null,
			'destination' => '/path/new',
			'code' => 200
		],
		'expected' => false
	],
];
