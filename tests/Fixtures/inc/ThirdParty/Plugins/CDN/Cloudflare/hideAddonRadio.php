<?php
return [
    'pluginDisabledShouldReturnSame' => [
        'config' => [
			  'active_plugins' => [
			  ],
              'enable' => true,
			  'plugin_active' => false,
			  'cloudflare_api_email' => 'email@test.test',
			  'cloudflare_api_key' => '1ef242',
			  'cloudflare_cached_domain_name' => 'domain',
		],
        'expected' => true
    ],
    'emptyEmailShouldReturnSame' => [
	    'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
		    'enable' => true,
		    'plugin_active' => true,
		    'cloudflare_api_email' => '',
		    'cloudflare_api_key' => '1ef242',
			'cloudflare_cached_domain_name' => 'domain',
		],
	    'expected' => true
    ],
    'emptyAPIKeyShouldReturnSame' => [
	    'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
		    'enable' => true,
		    'plugin_active' => true,
		    'cloudflare_api_email' => 'email@test.test',
		    'cloudflare_api_key' => '',
			'cloudflare_cached_domain_name' => 'domain',
		],
	    'expected' => true
    ],
	'emptyDomainShouldReturnSame' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
			'enable' => true,
			'plugin_active' => true,
			'cloudflare_api_email' => 'email@test.test',
			'cloudflare_api_key' => '1ef242',
			'cloudflare_cached_domain_name' => '',
		],
		'expected' => true
	],
	'shouldReturnFalse' => [
		'config' => [
			'active_plugins' => [
				'cloudflare/cloudflare.php'
			],
			'enable' => true,
			'plugin_active' => true,
			'cloudflare_api_email' => 'email@test.test',
			'cloudflare_api_key' => '1ef242',
			'cloudflare_cached_domain_name' => 'domain',
		],
		'expected' => false
	]
];
