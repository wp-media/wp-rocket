<?php
return [
	'pluginInactiveShouldNotUnregister' => [
		'config'   => [
			'plugin_active'                  => false,
			'cloudflare_api_email'           => 'email@test.test',
			'cloudflare_api_key'             => '1ef242',
			'cloudflare_cached_domain_name'  => 'domain',
		],
		'expected' => [
			'should_unregister' => false,
		],
	],
	'emptyEmailShouldNotUnregister'     => [
		'config'   => [
			'plugin_active'                  => true,
			'cloudflare_api_email'           => '',
			'cloudflare_api_key'             => '1ef242',
			'cloudflare_cached_domain_name'  => 'domain',
		],
		'expected' => [
			'should_unregister' => false,
		],
	],
	'emptyAPIKeyShouldNotUnregister'    => [
		'config'   => [
			'plugin_active'                  => true,
			'cloudflare_api_email'           => 'email@test.test',
			'cloudflare_api_key'             => '',
			'cloudflare_cached_domain_name'  => 'domain',
		],
		'expected' => [
			'should_unregister' => false,
		],
	],
	'emptyDomainShouldNotUnregister'    => [
		'config'   => [
			'plugin_active'                  => true,
			'cloudflare_api_email'           => 'email@test.test',
			'cloudflare_api_key'             => '1ef242',
			'cloudflare_cached_domain_name'  => '',
		],
		'expected' => [
			'should_unregister' => false,
		],
	],
	'pluginActiveShouldUnregister'      => [
		'config'   => [
			'plugin_active'                  => true,
			'cloudflare_api_email'           => 'email@test.test',
			'cloudflare_api_key'             => '1ef242',
			'cloudflare_cached_domain_name'  => 'domain',
		],
		'expected' => [
			'should_unregister' => true,
		],
	],
];
