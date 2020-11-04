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
			'is_grandfather' => 50,
			'not_grandfather'=> 30,
			'is_expired'     => 20,
		],
	] ) ),
];

$countdown = [
	'days'    => 0,
	'hours'   => 0,
	'minutes' => 0,
	'seconds' => 0,
];

return [
	'testShouldReturnNullWhenLicenseIsExpired' => [
		'config'   => [
			'user' => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'auto_renew'         => false,
				'licence_expiration' => strtotime( 'last year' ),
			],
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenLicenseAutoRenew' => [
		'config'   => [
			'user' => [
				'licence_account'    => 1,
				'licence_expired'    => false,
				'auto_renew'         => true,
				'licence_expiration' => strtotime( 'next week' ),
			],
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenLicenseNotExpireSoon' => [
		'config'   => [
			'user' => [
				'licence_account'    => 1,
				'licence_expired'    => false,
				'auto_renew'         => false,
				'licence_expiration' => strtotime( 'next year' ),
			],
		],
		'expected' => null,
	],
	'testShouldReturnDataWhenLicenseAndSingleAndNotGrandfathered' => [
		'config'   => [
			'user' => [
				'licence_account'    => 1,
				'licence_expired'    => false,
				'auto_renew'         => false,
				'licence_expiration' => strtotime( 'next week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( 'last year' ),
			],
			'pricing'   => $pricing,
		],
		'expected' => [
			'discount_percent' => 30,
			'discount_price'   => 34.3,
			'countdown'        => $countdown,
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
	'testShouldReturnDataWhenLicenseSingleAndGrandfathered' => [
		'config'   => [
			'user' => [
				'licence_account'    => 1,
				'licence_expired'    => false,
				'auto_renew'         => false,
				'licence_expiration' => strtotime( 'last week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2019/08/01' ),
			],
			'pricing'   => $pricing,
		],
		'expected' => [
			'discount_percent' => 50,
			'discount_price'   => 24.5,
			'countdown'        => $countdown,
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
	'testShouldReturnDataWhenLicensePlusAndNotGrandfathered' => [
		'config'   => [
			'user' => [
				'licence_account'    => 3,
				'licence_expired'    => false,
				'auto_renew'         => false,
				'licence_expiration' => strtotime( 'next week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( 'last year' ),
			],
			'pricing'   => $pricing,
		],
		'expected' => [
			'discount_percent' => 30,
			'discount_price'   => 69.3,
			'countdown'        => $countdown,
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
	'testShouldReturnDataWhenLicensePlusAndGrandfathered' => [
		'config'   => [
			'user' => [
				'licence_account'    => 3,
				'licence_expired'    => false,
				'auto_renew'         => false,
				'licence_expiration' => strtotime( 'next week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2019/08/01' ),
			],
			'pricing'   => $pricing,
		],
		'expected' => [
			'discount_percent' => 50,
			'discount_price'   => 49.5,
			'countdown'        => $countdown,
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
	'testShouldReturnDataWhenLicenseInfiniteAndNotGrandfathered' => [
		'config'   => [
			'user' => [
				'licence_account'    => -1,
				'licence_expired'    => false,
				'auto_renew'         => false,
				'licence_expiration' => strtotime( 'next week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( 'last year' ),
			],
			'pricing'   => $pricing,
		],
		'expected' => [
			'discount_percent' => 30,
			'discount_price'   => 174.3,
			'countdown'        => $countdown,
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
	'testShouldReturnDataWhenLicenseInfiniteAndGrandfathered' => [
		'config'   => [
			'user' => [
				'licence_account'    => -1,
				'licence_expired'    => false,
				'auto_renew'         => false,
				'licence_expiration' => strtotime( 'next week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2019/08/01' ),
			],
			'pricing'   => $pricing,
		],
		'expected' => [
			'discount_percent' => 50,
			'discount_price'   => 124.5,
			'countdown'        => $countdown,
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
];
