<?php

return [

	'testShouldSendErrorWhenCDNURLEmpty' => [
		'config'   => [
			'cdn_url' => null,
		],
		'expected' => [
			'response' => (object) [
				'success' => false,
				'data'    => (object) [
					'process' => 'subscribe',
					'message' => 'cdn_url_empty',
				],
			],
		],
	],

	'testShouldSendErrorWhenCDNURLInvalid' => [
		'config'   => [
			'cdn_url' => '%20%20',
		],
		'expected' => [
			'response' => (object) [
				'success' => false,
				'data'    => (object) [
					'process' => 'subscribe',
					'message' => 'cdn_url_invalid_format',
				],
			],
		],
	],

	'testShouldSendSuccessWhenCDNURLValid' => [
		'config'   => [
			'cdn_url' => 'https://rocketcdn.me',
		],
		'expected' => [
			'response' => (object) [
				'success' => true,
				'data'    => (object) [
					'process' => 'subscribe',
					'message' => 'rocketcdn_enabled',
				],
			],
			'settings' => [
				'cdn'        => 1,
				'cdn_cnames' => [ 'https://rocketcdn.me' ],
				'cdn_zone'   => [ 'all' ],
			],
		],
	],
];
