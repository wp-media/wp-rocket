<?php

return [
	'testShouldReturnSameWhenNocap' => [
		'config' => [
			'cap' => false,
			'error' => false,
			'result' => [],
			'value' => [
				'cloudflare_auto_settings' => 0,
				'cloudflare_old_settings' => '',
			],
			'old_value' => [
				'cloudflare_auto_settings' => 0,
				'cloudflare_old_settings' => '',
			],
 		],
		'expected' => [
			'cloudflare_auto_settings' => 0,
			'cloudflare_old_settings' => '',
		],
	],
	'testShouldReturnSameWhenAutoSettingsNotSet' => [
		'config' => [
			'cap' => true,
			'error' => false,
			'result' => [],
			'value' => [
				'cloudflare_old_settings' => '',
			],
			'old_value' => [
				'cloudflare_old_settings' => '',
			],
 		],
		'expected' => [
			'cloudflare_old_settings' => '',
		],
	],
	'testShouldReturnSameWhenAutoSettingsSameValue' => [
		'config' => [
			'cap' => true,
			'error' => false,
			'result' => [],
			'value' => [
				'cloudflare_auto_settings' => 0,
				'cloudflare_old_settings' => '',
			],
			'old_value' => [
				'cloudflare_auto_settings' => 0,
				'cloudflare_old_settings' => '',
			],
 		],
		'expected' => [
			'cloudflare_auto_settings' => 0,
			'cloudflare_old_settings' => '',
		],
	],
	'testShouldReturnSameWhenAutoSettingsZero' => [
		'config' => [
			'cap' => true,
			'error' => false,
			'result' => [],
			'value' => [
				'cloudflare_auto_settings' => 0,
				'cloudflare_old_settings' => '',
			],
			'old_value' => [
				'cloudflare_auto_settings' => 1,
				'cloudflare_old_settings' => '',
			],
 		],
		'expected' => [
			'cloudflare_auto_settings' => 0,
			'cloudflare_old_settings' => '',
		],
	],
	'testShouldReturnUpdatedEmptyWhenError' => [
		'config' => [
			'cap' => true,
			'error' => true,
			'result' => [],
			'value' => [
				'cloudflare_auto_settings' => 1,
			],
			'old_value' => [
				'cloudflare_auto_settings' => 0,
			],
 		],
		'expected' => [
			'cloudflare_auto_settings' => 1,
			'cloudflare_old_settings' => '',
		],
	],
	'testShouldReturnUpdatedWhenSuccess' => [
		'config' => [
			'cap' => true,
			'error' => false,
			'result' => [
				'cache_level'       => 'aggressive',
				'minify'            => 'on',
				'rocket_loader'     => 'off',
				'browser_cache_ttl' => 14400,
			],
			'value' => [
				'cloudflare_auto_settings' => 1,
			],
			'old_value' => [
				'cloudflare_auto_settings' => 0,
			],
 		],
		'expected' => [
			'cloudflare_auto_settings' => 1,
			'cloudflare_old_settings' => 'aggressive,on,off,14400',
		],
	],
];
