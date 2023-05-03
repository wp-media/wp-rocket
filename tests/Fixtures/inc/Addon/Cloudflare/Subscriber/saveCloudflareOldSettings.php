<?php

return [
	'testShouldReturnSameWhenNocap' => [
		'config' => [
			'cap' => false,
			'error' => false,
			'response' => [],
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
			'response' => [],
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
			'response' => [],
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
			'response' => [],
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
			'response' => new WP_Error( 'error' ),
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
			'response' => [
				'headers' => [],
				'body' => json_encode( (object) [
					'success' => true,
					'result' => [
						(object) [
							'id' =>'browser_cache_ttl',
							'value'=> 14400 ,
						],
						(object) [
							'id' =>'cache_level',
							'value'=> 'aggressive',
						],
						(object) [
							'id' =>'rocket_loader',
							'value'=> 'off',
						],
						(object) [
							'id' =>'minify',
							'value'=> (object) [
								'js' => 'on',
								'css' => 'on',
								'html' => 'off',
							],
						],
					],
				] ),
				'cookies' => [],
			],
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
