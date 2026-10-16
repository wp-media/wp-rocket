<?php
return [
	'shouldLogOnWPError' => [
		'config' => [
			'is_wp_error' => true,
			'error_message' => 'cURL error 28: Operation timed out',
			'log' => 'Sitemap fetch failed for http://example.com: cURL error 28: Operation timed out',
			'is_excluded' => false,
			'url' => 'http://example.com',
			'response' => 'response',
			'request_succeed' => false,
			'status' => 0,
			'content' => '',
			'links' => [],
			'jobs' => [],
		],
	],
	'shouldLogOnNon200' => [
		'config' => [
			'is_wp_error' => false,
			'log' => 'Sitemap fetch failed for http://example.com: unexpected response code 404',
			'is_excluded' => false,
			'url' => 'http://example.com',
			'response' => 'response',
			'request_succeed' => false,
			'status' => 404,
			'content' => '',
			'links' => [],
			'jobs' => [],
		],
	],
	'shouldLogOnEmptyBody' => [
		'config' => [
			'is_wp_error' => false,
			'log' => 'Sitemap fetch failed for http://example.com: empty response body',
			'is_excluded' => false,
			'url' => 'http://example.com',
			'response' => 'response',
			'request_succeed' => false,
			'status' => 200,
			'content' => '',
			'links' => [],
			'jobs' => [],
		],
	],
	'shouldNoParseOnRequestFailure' => [
		'config' => [
			'is_wp_error' => false,
			'log' => 'Sitemap fetch failed for http://example.com: unexpected response code 400',
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
			'is_wp_error' => false,
			'log' => '',
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
			'is_wp_error' => false,
			'log' => '',
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
	]
];
