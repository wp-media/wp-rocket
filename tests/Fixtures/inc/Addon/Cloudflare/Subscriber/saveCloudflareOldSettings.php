<?php

return [
	'testShouldReturnSameWhenNocap' => [
		'config' => [
			'cap' => false,
			'error' => false,
			'response' => [],
			'result' => [],
			'cloudflare_zone_id' => 'cf_zone_id',
			'value' => [
				'cloudflare_auto_settings' => 0,
				'cloudflare_old_settings' => '',
				'cloudflare_zone_id' => 'cf_zone_id',
			],
			'old_value' => [
				'cloudflare_auto_settings' => 0,
				'cloudflare_old_settings' => '',
			],
        ],
		'expected' => [
			'cloudflare_auto_settings' => 0,
			'cloudflare_old_settings' => '',
			'cloudflare_zone_id' => 'cf_zone_id',
		],
	],
	'testShouldReturnSameWhenAutoSettingsNotSet' => [
		'config' => [
			'cap' => true,
			'error' => false,
			'response' => [],
			'result' => [],
			'cloudflare_zone_id' => 'cf_zone_id',
			'value' => [
				'cloudflare_zone_id' => 'cf_zone_id',
				'cloudflare_old_settings' => '',
			],
			'old_value' => [
				'cloudflare_old_settings' => '',
			],
        ],
		'expected' => [
			'cloudflare_zone_id' => 'cf_zone_id',
			'cloudflare_old_settings' => '',
		],
	],
	'testShouldReturnSameWhenAutoSettingsSameValue' => [
		'config' => [
			'cap' => true,
			'error' => false,
			'response' => [],
			'result' => [],
			'cloudflare_zone_id' => 'cf_zone_id',
			'value' => [
				'cloudflare_auto_settings' => 0,
				'cloudflare_old_settings' => '',
				'cloudflare_zone_id' => 'cf_zone_id',
			],
			'old_value' => [
				'cloudflare_auto_settings' => 0,
				'cloudflare_old_settings' => '',
			],
        ],
		'expected' => [
			'cloudflare_auto_settings' => 0,
			'cloudflare_old_settings' => '',
			'cloudflare_zone_id' => 'cf_zone_id',
		],
	],
	'testShouldReturnSameWhenAutoSettingsZero' => [
		'config' => [
			'cap' => true,
			'error' => false,
			'response' => [],
			'result' => [],
			'cloudflare_zone_id' => 'cf_zone_id',
			'value' => [
				'cloudflare_auto_settings' => 0,
				'cloudflare_old_settings' => '',
				'cloudflare_zone_id' => 'cf_zone_id',
			],
			'old_value' => [
				'cloudflare_auto_settings' => 1,
				'cloudflare_old_settings' => '',
			],
        ],
		'expected' => [
			'cloudflare_auto_settings' => 0,
			'cloudflare_old_settings' => '',
			'cloudflare_zone_id' => 'cf_zone_id',
		],
	],
	'testShouldReturnUpdatedEmptyWhenError' => [
		'config' => [
			'cap' => true,
			'error' => true,
			'response' => new WP_Error( 'error' ),
			'result' => [],
			'cloudflare_zone_id' => 'cf_zone_id',
			'value' => [
				'cloudflare_auto_settings' => 1,
				'cloudflare_zone_id' => 'cf_zone_id',
			],
			'old_value' => [
				'cloudflare_auto_settings' => 0,
			],
        ],
		'expected' => [
			'cloudflare_auto_settings' => 1,
			'cloudflare_zone_id' => 'cf_zone_id',
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
								'html' => 'on',
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
			'cloudflare_zone_id' => 'cf_zone_id',
			'value' => [
				'cloudflare_auto_settings' => 1,
				'cloudflare_zone_id' => 'cf_zone_id',
			],
			'old_value' => [
				'cloudflare_auto_settings' => 0,
			],
        ],
		'expected' => [
			'cloudflare_auto_settings' => 1,
			'cloudflare_zone_id' => 'cf_zone_id',
			'cloudflare_old_settings' => 'aggressive,on,off,14400',
		],
	],
];
