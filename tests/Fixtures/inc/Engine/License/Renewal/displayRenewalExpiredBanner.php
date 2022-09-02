<?php

$pricing = [
	'single'=> json_decode( json_encode( [
		'prices'=> [
			'renewal'=> [
				'is_grandfather'=> 24.5,
				'not_grandfather'=> 34.3,
				'is_expired'=> 39.2
			]
		],
		'websites'=> 1
	] ) ),
	'plus'=> json_decode( json_encode( [
		'prices'=> [
			'renewal'=> [
				'is_grandfather'=> 49.5,
				'not_grandfather'=> 69.3,
				'is_expired'=> 79.2
			]
		],
		'websites'=> 3
	] ) ),
	'infinite'=> json_decode( json_encode( [
		'prices'=> [
			'renewal'=> [
				'is_grandfather'=> 124.5,
				'not_grandfather'=> 174.3,
				'is_expired'=> 199.2
			]
		],
	] ) ),
	'renewals' => json_decode( json_encode( [
		'extra_days'=> 90,
		'grandfather_date'=> 1567296000,
		'discount_percent'=> [
			'is_grandfather' => 20,
			'not_grandfather'=> 0,
			'is_expired'     => 0,
		],
	] ) ),
];

return [
	'testShouldReturnNullWhenLicenseIsNotExpired' => [
		'config'   => [
			'user' => [
				'licence_account'    => 1,
				'licence_expired'    => false,
				'licence_expiration' => strtotime( 'next year' ),
				'auto_renew' => false,
			],
			'ocd' => false,
			'transient' => false,
			'pricing' => $pricing,
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenBannerDismissed' => [
		'config'   => [
			'user' => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'last year' ),
				'auto_renew' => false,
			],
			'ocd' => false,
			'transient' => true,
			'pricing' => $pricing,
		],
		'expected' => null,
	],
	'testShouldReturnDataWhenLicenseExpired' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'last year' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( 'last year' ),
				'auto_renew' => false,
			],
			'ocd' => false,
			'transient' => false,
			'pricing' => $pricing,
		],
		'expected' => [
			'template' => 'renewal-expired-banner',
			'data' => [
				'renewal_url'   => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'renewal_price' => '34.3',
			],
		],
	],
	'shouldReturnDataWhenLicenseExpiredAndOCDEnabled' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'now - 20 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( 'last year' ),
				'auto_renew' => false,
			],
			'ocd' => 1,
			'transient' => false,
			'pricing' => $pricing,
		],
		'expected' => [
			'template' => 'renewal-expired-banner-ocd-disabled',
			'data' => [
				'renewal_url' => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'renewal_price' => '34.3',
			],
		],
	],
	'shouldReturnDataWhenLicenseExpiredRecentlyAndOCDEnabled' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'now - 7 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( 'last year' ),
				'auto_renew' => false,
			],
			'ocd' => 1,
			'transient' => false,
			'pricing' => $pricing,
		],
		'expected' => [
			'template' => 'renewal-expired-banner-ocd',
			'data' => [
				'renewal_url' => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'renewal_price' => '34.3',
				'disabled_date' => date( 'Ymd', strtotime( 'now + 8 days' ) ),
			],
		],
	],
];
