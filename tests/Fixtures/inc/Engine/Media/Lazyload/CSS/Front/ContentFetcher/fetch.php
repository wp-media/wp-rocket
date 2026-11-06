<?php
return [
    'PathShouldUseFilesystem' => [
        'config' => [
              'path' => '/my/file',
			  'is_url' => false,
			  'content' => 'content',
			  'destination' => '/path/new'
        ],
        'expected' => 'content'
    ],
	'UrlShouldUseRemoteGet' => [
		'config' => [
			'path' => 'http://example.org/test',
			'is_url' => true,
			'content' => 'content',
			'response' => 'response',
			'code' => 200,
			'content_type' => 'text/css',
			'body' => 'body',
			'destination' => '/path/new'
		],
		'expected' => 'body'
	],
	'UrlContentNUllShouldReturnFalse' => [
		'config' => [
			'path' => 'http://example.org/test',
			'is_url' => true,
			'content' => 'content',
			'response' => 'response',
			'code' => 200,
			'body' => null,
			'destination' => '/path/new'
		],
		'expected' => false
	],
	'UrlNotFoundShouldReturnFalse' => [
		'config' => [
			'path' => 'http://example.org/missing.css',
			'is_url' => true,
			'content' => 'content',
			'response' => 'response',
			'code' => 404,
			'body' => '<html><style>body{display:none}</style></html>',
			'destination' => '/path/new'
		],
		'expected' => false
	],
	'UrlServedAsHtmlShouldReturnFalse' => [
		'config' => [
			'path' => 'http://example.org/soft-404.css',
			'is_url' => true,
			'content' => 'content',
			'response' => 'response',
			'code' => 200,
			'content_type' => 'text/html; charset=UTF-8',
			'body' => '<html><style>body{display:none}</style></html>',
			'destination' => '/path/new'
		],
		'expected' => false
	],
];
