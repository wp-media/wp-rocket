<?php

return [
	'testShouldReturnNullWhenNocap' => [
		'config' => [
			'cap' => false,
			'connection' => false,
			'transient' => true,
			'error' => false,
			'devmode' => [],
			'value' => [
				'cloudflare_zone_id' => '12345',
			],
			'old_value' => [
				'cloudflare_zone_id' => '12345',
			],
        ],
		'expected' => null,
	],
	'testShouldReturnNullWhenError' => [
		'config' => [
			'cap' => true,
			'connection' => true,
			'transient' => new WP_Error( 400, 'message' ),
			'error' => true,
			'devmode' => [],
			'value' => [
				'cloudflare_zone_id' => '12345',
				'cloudflare_auto_settings' => 1
			],
			'old_value' => [
				'cloudflare_zone_id' => '12345',
				'cloudflare_auto_settings' => 0
			],
        ],
		'expected' => 'error',
	],
	'testShouldSetTransientWhenSuccess' => [
		'config' => [
			'cap' => true,
			'connection' => false,
			'transient' => true,
			'error' => false,
			'devmode' => [],
			'value' => [
				'cloudflare_zone_id' => '12345',
				'cloudflare_devmode' => 0,
				'cloudflare_auto_settings' => 1,
				'cloudflare_old_settings' => '',
			],
			'old_value' => [
				'cloudflare_zone_id' => '12345',
				'cloudflare_devmode' => 0,
				'cloudflare_auto_settings' => 0,
				'cloudflare_old_settings' => '',
			],
        ],
		'expected' => [
			'pre' => '<strong>WP Rocket:</strong> Optimal settings deactivated for Cloudflare, reverted to previous settings.',
		],
	],
];
