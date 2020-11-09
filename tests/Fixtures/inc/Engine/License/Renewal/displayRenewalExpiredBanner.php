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

return [
	'testShouldReturnNullWhenLicenseIsNotExpired' => [
		'config'   => [
			'user' => [
				'licence_account'    => 1,
				'licence_expired'    => false,
				'licence_expiration' => strtotime( 'next year' ),
			],
			'transient' => false,
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenBannerDismissed' => [
		'config'   => [
			'user' => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'last year' ),
			],
			'transient' => true,
		],
		'expected' => null,
	],
	'testShouldReturnDataWhenLicenseExpiredForMoreThan90DaysAndSingleAndNotGrandfathered' => [
		'config'   => [
			'user' => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'last year' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( 'last year' ),
			],
			'transient' => false,
			'pricing'   => $pricing,
		],
		'expected' => [
			'discount_percent' => 20,
			'discount_price'   => 39.2,
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
	'testShouldReturnDataWhenLicenseExpiredForLessThan90DaysAndSingleAndNotGrandfathered' => [
		'config'   => [
			'user' => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'last week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( 'last year' ),
			],
			'transient' => false,
			'pricing'   => $pricing,
		],
		'expected' => [
			'discount_percent' => 30,
			'discount_price'   => 34.3,
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
	'testShouldReturnDataWhenLicenseExpiredForLessThan90DaysAndSingleAndGrandfathered' => [
		'config'   => [
			'user' => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'last week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2019/08/01' ),
			],
			'transient' => false,
			'pricing'   => $pricing,
		],
		'expected' => [
			'discount_percent' => 50,
			'discount_price'   => 24.5,
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
	'testShouldReturnDataWhenLicenseExpiredForMoreThan90DaysAndPlusAndNotGrandfathered' => [
		'config'   => [
			'user' => [
				'licence_account'    => 3,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'last year' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( 'last year' ),
			],
			'transient' => false,
			'pricing'   => $pricing,
		],
		'expected' => [
			'discount_percent' => 20,
			'discount_price'   => 79.2,
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
	'testShouldReturnDataWhenLicenseExpiredForLessThan90DaysAndPlusAndNotGrandfathered' => [
		'config'   => [
			'user' => [
				'licence_account'    => 3,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'last week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( 'last year' ),
			],
			'transient' => false,
			'pricing'   => $pricing,
		],
		'expected' => [
			'discount_percent' => 30,
			'discount_price'   => 69.3,
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
	'testShouldReturnDataWhenLicenseExpiredForLessThan90DaysAndPlusAndGrandfathered' => [
		'config'   => [
			'user' => [
				'licence_account'    => 3,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'last week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2019/08/01' ),
			],
			'transient' => false,
			'pricing'   => $pricing,
		],
		'expected' => [
			'discount_percent' => 50,
			'discount_price'   => 49.5,
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
	'testShouldReturnDataWhenLicenseExpiredForMoreThan90DaysAndInfiniteAndNotGrandfathered' => [
		'config'   => [
			'user' => [
				'licence_account'    => -1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'last year' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( 'last year' ),
			],
			'transient' => false,
			'pricing'   => $pricing,
		],
		'expected' => [
			'discount_percent' => 20,
			'discount_price'   => 199.2,
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
	'testShouldReturnDataWhenLicenseExpiredForLessThan90DaysAndInfiniteAndNotGrandfathered' => [
		'config'   => [
			'user' => [
				'licence_account'    => -1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'last week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( 'last year' ),
			],
			'transient' => false,
			'pricing'   => $pricing,
		],
		'expected' => [
			'discount_percent' => 30,
			'discount_price'   => 174.3,
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
	'testShouldReturnDataWhenLicenseExpiredForLessThan90DaysAndInfiniteAndGrandfathered' => [
		'config'   => [
			'user' => [
				'licence_account'    => -1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'last week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2019/08/01' ),
			],
			'transient' => false,
			'pricing'   => $pricing,
		],
		'expected' => [
			'discount_percent' => 50,
			'discount_price'   => 124.5,
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
];
