<?php

$body = <<<HTML
<html>
<head>
</head>
<body>
<a href="url1">test</a>
<a href="url2">test</a>
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
			'url1',
			'url2',
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
