<?php

use WP_Rocket\Addon\Cloudflare\Auth\AuthInterface;

return [
	'testShouldDoNothingWhenNoCap' => [
		'config' => [
			'cap' => false,
			'error' => false,
			'page_rule' => true,
			'settings' => [
				'cloudflare_zone_id' => true
			],
			'cloudflare_zone_id' => 'cf_id',
			'auth' => Mockery::mock(AuthInterface::class)
		],
		'expected' => null,
	],
	'testShouldDoNothingWhenNoRule' => [
		'config' => [
			'cap' => true,
			'error' => false,
			'page_rule' => false,
			'settings' => [
				'cloudflare_zone_id' => true
			],
			'cloudflare_zone_id' => 'cf_id',
			'auth' => Mockery::mock(AuthInterface::class)
		],
		'expected' => null,
	],
	'testShouldDoNothingWhenError' => [
		'config' => [
			'cap' => true,
			'error' => true,
			'page_rule' => true,
			'settings' => [
				'cloudflare_zone_id' => true
			],
			'cloudflare_zone_id' => 'cf_id',
			'auth' => Mockery::mock(AuthInterface::class)
		],
		'expected' => null,
	],
	'testShouldPurgeWhenHasRule' => [
		'config' => [
			'cap' => true,
			'error' => false,
			'page_rule' => true,
			'settings' => [
				'cloudflare_zone_id' => true
			],
			'cloudflare_zone_id' => 'cf_id',
			'auth' => Mockery::mock(AuthInterface::class)
		],
		'expected' => 'expected',
	],
];
