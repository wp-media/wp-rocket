<?php
return [
	'alreadyPresentShouldDoNothing' => [
		'config' => [
			'cached_queries' => [
				'test'
			],
			'is_cached' => true,
			'query_activated' => true,
			'url' => 'http://example.org',
			'cache_exists' => true,
			'cache_mobile' => false,
			'user_agent' => 'user_agent',
			'request' => [
				'config' => [
					'blocking' => false,
					'timeout'  => 0.01,
					'user-agent' => 'WP Rocket/Preload',
					'sslverify' => false,
				]
			],
		]
	],
	'mobileNotActivatedShouldPreloadOnlyOnce' => [
		'config' => [
			'cached_queries' => [
				'test'
			],
			'is_cached' => true,
			'query_activated' => true,
			'url' => 'http://example.org',
			'cache_exists' => false,
			'cache_mobile' => false,
			'user_agent' => 'user_agent',
			'request' => [
				'config' => [
					'blocking' => false,
					'timeout'  => 0.01,
					'user-agent' => 'WP Rocket/Preload',
					'sslverify' => false,
				]
			],
		]
	],
	'mobileActivatedShouldPreloadTwice' => [
		'config' => [
			'cached_queries' => [
				'test'
			],
			'is_cached' => true,
			'query_activated' => true,
			'url' => 'http://example.org',
			'cache_exists' => false,
			'cache_mobile' => true,
			'user_agent' => 'user_agent',
			'request' => [
				'config' => [
					'blocking' => false,
					'timeout'  => 0.01,
					'user-agent' => 'WP Rocket/Preload',
					'sslverify' => false,
				]
			],
			'request_mobile' => [
				'config' => [
					'user-agent' => 'user_agent',
					'blocking' => false,
					'timeout'  => 0.01,
					'sslverify' => false,
				]
			],
		]
	],
	'paramAndCachedShouldPreload' => [
		'config' => [
			'cached_queries' => [
				'test'
			],
			'is_cached' => true,
			'query_activated' => true,
			'url' => 'http://example.org?test=1',
			'cache_exists' => false,
			'cache_mobile' => true,
			'user_agent' => 'user_agent',
			'request' => [
				'config' => [
					'blocking' => false,
					'timeout'  => 0.01,
					'user-agent' => 'WP Rocket/Preload',
					'sslverify' => false,
				]
			],
			'request_mobile' => [
				'config' => [
					'blocking' => false,
					'timeout'  => 0.01,
					'user-agent' => 'user_agent',
					'sslverify' => false,
				]
			],
		]
	],
	'paramAndNotCachedShouldBailout' => [
		'config' => [
			'cached_queries' => [
				'test'
			],
			'is_cached' => false,
			'query_activated' => true,
			'url' => 'http://example.org?a=a',
			'cache_exists' => false,
			'cache_mobile' => true,
			'user_agent' => 'user_agent',
			'request' => [
				'config' => [
					'blocking' => false,
					'timeout'  => 0.01,
					'user-agent' => 'WP Rocket/Preload',
					'sslverify' => false,
				]
			],
			'request_mobile' => [
				'config' => [
					'blocking' => false,
					'timeout'  => 0.01,
					'user-agent' => 'user_agent',
					'sslverify' => false,
				]
			],
		]
	],
];
