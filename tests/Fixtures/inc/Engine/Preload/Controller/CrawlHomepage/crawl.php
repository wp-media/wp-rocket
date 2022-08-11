<?php

$body = <<<HTML
<html>
<head>
</head>
<body>
<a href="home_url/url1">test</a>
<a href="home_url/url2">test</a>
<a href="home_url/#url4">test</a>
<a href="url3">test</a>
</body>
</html>
HTML;

return [
	'shouldReturnUrlsOnSuccess' => [
		'config' => [
			'home_url' => 'home_url',
			'escaped_home_url' => 'escaped_home_url',
			'request' => [
				'response' => 'response',
				'is_error' => false,
				'code' => 200,
				'body' => $body
			],
			'args' => [
				'timeout'    => 10,
				'user-agent' => 'WP Rocket/Preload',
				'sslverify'  => false
			]
		],
		'expected' => [
			'home_url/url1',
			'home_url/url2',
		]
	],
	'shouldReturnNothingOnWPError' => [
		'config' => [
			'home_url' => 'home_url',
			'escaped_home_url' => 'escaped_home_url',
			'request' => [
				'response' => 'response',
				'is_error' => true,
				'code' => 200,
				'body' => $body
			],
			'args' => [
				'timeout'    => 10,
				'user-agent' => 'WP Rocket/Preload',
				'sslverify'  => false
			]
		],
		'expected' => false
	],
	'shouldReturnNothingOnNoSuccessResponse' => [
		'config' => [
			'home_url' => 'home_url',
			'escaped_home_url' => 'escaped_home_url',
			'request' => [
				'response' => 'response',
				'is_error' => false,
				'code' => 404,
				'body' => $body
			],
			'args' => [
				'timeout'    => 10,
				'user-agent' => 'WP Rocket/Preload',
				'sslverify'  => false
			]
		],
		'expected' => false
	]
];
