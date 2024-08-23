<?php
return [
	'testShouldCallSendToSaas' => [
		'config'   => [
			'wp_env' => 'production',
			'remove_unused_css' => 0,
			'factories' => [1],
			'license_expired' => false,
			'home_url' =>  'http://example.com/',
		],
		'expected' => 1,
	],
	'testShouldNotCallSendToSaasWhenLicenseExpired' => [
		'config'   => [
			'wp_env' => 'production',
			'remove_unused_css' => 0,
			'factories' => [1],
			'license_expired' => true,
			'home_url' =>  'http://example.com/',
		],
		'expected' => 0,
	],
	'testShouldNotCallSendToSaasWhenLocalEnv' => [
		'config'   => [
			'wp_env' => 'local',
			'remove_unused_css' => 0,
			'factories' => [1],
			'license_expired' => false,
			'home_url' =>  'http://example.com/',
		],
		'expected' => 0,
	],
	'testShouldNotCallSendToSaasWhenRemoveUnusedCssEnabled' => [
		'config'   => [
			'wp_env' => 'production',
			'remove_unused_css' => 1,
			'license_expired' => false,
			'factories' => [1],
			'home_url' =>  'http://example.com/',
		],
		'expected' => 0,
	],
	'testShouldNotCallSendToSaasWhenFactoriesAreEmpty' => [
		'config'   => [
			'wp_env' => 'production',
			'remove_unused_css' => 0,
			'license_expired' => false,
			'factories' => [],
			'home_url' =>  'http://example.com/',
		],
		'expected' => 0,
	],
];
