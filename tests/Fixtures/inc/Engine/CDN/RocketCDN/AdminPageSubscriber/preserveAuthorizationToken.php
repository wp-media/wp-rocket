<?php

return [
	'testShouldReturnSameArgsWhenURLNotRocketCDN' => [
		'config'   => [
			'url' => 'http://example.org',
		],
		'expected' => [
			'method'  => 'GET',
			'headers' => [],
			'body'    => '',
		],
	],

	'testShouldReturnSameArgsWhenAuthorizationHeadersEmptyAndEndpointIsPricing' => [
		'config'   => [
			'url' => 'https://rocketcdn.me/api/pricing',
		],
		'expected' => [
			'method'  => 'GET',
			'headers' => [],
			'body'    => '',
		],
	],

	'testShouldReturnSameArgsWhenAuthorizationHeadersCorrect' => [
		'config'   => [
			'url'              => 'https://rocketcdn.me/api/',
			'getApiCredential' => true,
		],
		'expected' => [
			'method'  => 'GET',
			'headers' => [
				'Authorization' => 'token ',
			],
			'body'    => '',
		],
	],

	'testShouldReturnCorrectTokenWhenAuthorizationHeadersEmpty' => [
		'config'   => [
			'url'              => 'https://rocketcdn.me/api/',
			'getApiCredential' => true,
		],
		'expected' => [
			'method'  => 'GET',
			'headers' => [
				'Authorization' => 'token ',
			],
			'body'    => '',
		],
		'sent'     => [
			'method'  => 'GET',
			'headers' => [],
			'body'    => '',
		],
	],

	'testShouldReturnCorrectTokenWhenAuthorizationHeadersIncorrect' => [
		'config'   => [
			'url'              => 'https://rocketcdn.me/api/',
			'getApiCredential' => true,
		],
		'expected' => [
			'method'  => 'GET',
			'headers' => [
				'Authorization' => 'token ',
			],
			'body'    => '',
		],
		'sent'     => [
			'method'  => 'GET',
			'headers' => [
				'Authorization' => 'token ABCD',
			],
			'body'    => '',
		],
	],
];
