<?php
return [
    'pluginDisabledShouldReturnSame' => [
        'config' => [
			'enabled' => false,
			'plugin_active' => false,
			'cloudflare_api_email' => 'email@test.test',
			'cloudflare_api_key' => '1ef242',
			'cloudflare_cached_domain_name' => 'domain',
		],
	    'expected' => [
			'enabled' => false,
	    ]
    ],
	'emptyEmailShouldReturnSame' => [
		'config' => [
			'enabled' => false,
			'plugin_active' => true,
			'cloudflare_api_email' => '',
			'cloudflare_api_key' => '1ef242',
			'cloudflare_cached_domain_name' => 'domain',
		],
		'expected' => [
			'enabled' => false,
		]
	],
	'emptyAPIKeyShouldReturnSame' => [
		'config' => [
			'enabled' => false,
			'plugin_active' => true,
			'cloudflare_api_email' => 'email@test.test',
			'cloudflare_api_key' => '',
			'cloudflare_cached_domain_name' => 'domain',
		],
		'expected' => [
			'enabled' => false,
		]
	],
	'emptyDomainShouldReturnSame' => [
		'config' => [
			'enabled' => false,
			'plugin_active' => true,
			'cloudflare_api_email' => 'email@test.test',
			'cloudflare_api_key' => '1ef242',
			'cloudflare_cached_domain_name' => '',
		],
		'expected' => [
			'enabled' => false,
		]
	],
	'shouldReturnFalse' => [
		'config' => [
			'enabled' => true,
			'plugin_active' => true,
			'cloudflare_api_email' => 'email@test.test',
			'cloudflare_api_key' => '1ef242',
			'cloudflare_cached_domain_name' => 'domain',
		],
		'expected' => [
			'enabled' => false,
		]
	]
];
