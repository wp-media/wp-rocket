<?php
return [
	'shouldNoParseOnRequestFailure' => [
		'config' => [
			'is_excluded' => false,
			'url' => 'http://example.com',
			'response' => 'response',
			'request_succeed' => false,
			'status' => 400,
			'content' => '',
			'links' => [],
			'jobs' => [],
		],
	],
	'shouldParseOnRequestSucceed' => [
		'config' => [
			'is_excluded' => false,
			'url' => 'http://example.com',
			'response' => 'response',
			'request_succeed' => true,
			'status' => 200,
			'content' => '<xml/>',
			'links' => [
				'url1',
				'url2',
			],
			'jobs' => [
				[
					['url' => 'url1',]
				],
				[
					['url' => 'url2',]
				]
			],
			'children' => [
				'children1',
				'children2',
			]
		],
	],
	'shouldNotAddOnExcluded' => [
		'config' => [
			'is_excluded' => true,
			'url' => 'http://example.com',
			'response' => 'response',
			'request_succeed' => true,
			'status' => 200,
			'content' => '<xml/>',
			'links' => [
				'url1',
				'url2',
			],
			'jobs' => [],
			'children' => [
				'children1',
				'children2',
			]
		],
	],
	'shouldNotAddIfPrivateUrl' => [
		'config' => [
			'is_excluded' => false,
			'url' => 'http://example.com',
			'response' => 'response',
			'request_succeed' => true,
			'status' => 200,
			'content' => '<xml/>',
			'links' => [
				'url1',
				'url2',
			],
			'jobs' => [],
			'children' => [
				'children1',
				'children2',
			]
		],
	]
];
