<?php
return [
	'capabilityDenied'         => [
		'config' => [
			'has_cap'            => false,
			'global_set'         => false,
			'nonce_present'      => false,
			'valid_nonce_action' => null,
			'cache_purge_url'    => null,
			'expect'             => 'none',
		],
	],
	'globalAbsent'              => [
		'config' => [
			'has_cap'            => true,
			'global_set'         => false,
			'nonce_present'      => false,
			'valid_nonce_action' => null,
			'cache_purge_url'    => null,
			'expect'             => 'none',
		],
	],
	'nonceMissing'              => [
		'config' => [
			'has_cap'            => true,
			'global_set'         => true,
			'nonce_present'      => false,
			'valid_nonce_action' => null,
			'cache_purge_url'    => null,
			'expect'             => 'none',
		],
	],
	'validPurgeUrlNonce'        => [
		'config' => [
			'has_cap'            => true,
			'global_set'         => true,
			'nonce_present'      => true,
			'valid_nonce_action' => 'sp-accel-purge-url',
			'cache_purge_url'    => 'https://example.com/some-page/',
			'expect'             => 'clean_files',
		],
	],
	'validPurgeThemeNonce'      => [
		'config' => [
			'has_cap'            => true,
			'global_set'         => true,
			'nonce_present'      => true,
			'valid_nonce_action' => 'sp-accel-purge-theme',
			'cache_purge_url'    => null,
			'expect'             => 'clean_domain',
		],
	],
	'invalidNonce'              => [
		'config' => [
			'has_cap'            => true,
			'global_set'         => true,
			'nonce_present'      => true,
			'valid_nonce_action' => null,
			'cache_purge_url'    => null,
			'expect'             => 'none',
		],
	],
	'validUrlNonceButEmptyUrl' => [
		'config' => [
			'has_cap'            => true,
			'global_set'         => true,
			'nonce_present'      => true,
			'valid_nonce_action' => 'sp-accel-purge-url',
			'cache_purge_url'    => null,
			'expect'             => 'none',
		],
	],
];
