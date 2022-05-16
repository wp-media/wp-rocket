<?php
return [
	'shouldNoParseOnRequestFailure' => [
		'config' => [
			'url' => 'http://example.com',
			'response' => 'response',
			'status' => 400,
			'content' => '',
			'links' => [],
			'jobs' => [],
		],
	],
	'shouldParseOnRequestSucceed' => [
		'config' => [
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
	]
];
