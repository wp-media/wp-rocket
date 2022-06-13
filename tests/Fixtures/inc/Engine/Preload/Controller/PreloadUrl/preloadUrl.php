<?php
return [
	'alreadyPresentShouldDoNothing' => [
		'config' => [
			'url' => 'url',
			'cache_exists' => true,
			'cache_mobile' => false,
			'user_agent' => 'user_agent',
			'request' => [
				'config' => [
					'blocking' => false,
					'timeout'  => 0.01,
				]
			],
		]
	],
	'mobileNotActivatedShouldPreloadOnlyOnce' => [
		'config' => [
			'url' => 'url',
			'cache_exists' => false,
			'cache_mobile' => false,
			'user_agent' => 'user_agent',
			'request' => [
				'config' => [
					'blocking' => false,
					'timeout'  => 0.01,
				]
			],
		]
	],
	'mobileActivatedShouldPreloadTwice' => [
		'config' => [
			'url' => 'url',
			'cache_exists' => false,
			'cache_mobile' => true,
			'user_agent' => 'user_agent',
			'request' => [
				'config' => [
					'blocking' => false,
					'timeout'  => 0.01,
				]
			],
			'request_mobile' => [
				'config' => [
					'blocking' => false,
					'timeout'  => 0.01,
					'user-agent' => 'user_agent'
				]
			],
		]
	]
];
