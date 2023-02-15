<?php

$pricing = [
	'single'=> json_decode( json_encode( [
		'prices'=> [
			'renewal'=> [
				'is_grandfather'=> 24.5,
				'is_grandmother'=> 24.5,
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
				'is_grandmother'=> 49.5,
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
				'is_grandmother'=> 124.5,
				'not_grandfather'=> 174.3,
				'is_expired'=> 199.2
			]
		],
	] ) ),
	'renewals' => json_decode( json_encode( [
		'extra_days'=> 90,
		'grandfather_date'=> 1567296000,
		'grandmother_date'=> 1672389000,
		'discount_percent'=> [
			'is_grandfather' => 20,
			'not_grandfather'=> 0,
			'is_expired'     => 0,
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
				'creation_date'      => strtotime( '2022-05-12' ),
			],
		],
		'expected' => null,
	],
	'testShouldReturnNullAutoRenewalIsEmpty' => [
		'config'   => [
			'user' => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'auto_renew'         => '',
				'licence_expiration' => strtotime( 'last year' ),
				'creation_date'      => strtotime( '2022-05-12' ),
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
				'creation_date'      => strtotime( '2022-05-12' ),
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
				'creation_date'      => strtotime( '2022-05-12' ),
			],
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenNoCreationDateIsZero' => [
		'config'   => [
			'user' => [
				'licence_account'    => 1,
				'licence_expired'    => false,
				'auto_renew'         => false,
				'licence_expiration' => strtotime( 'next week' ),
				'creation_date'		 => 0,
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
				'creation_date'      => strtotime( 'this year' ),
			],
			'pricing'   => $pricing,
		],
		'expected' => [
			'countdown'        => $countdown,
			'message' => 'Renew before it is too late, you will only pay <strong>$34.3</strong>!',
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
	'testShouldReturnDataWhenLicenseAndSingleAndGrandmothered' => [
		'config'   => [
			'user' => [
				'licence_account'    => 1,
				'licence_expired'    => false,
				'auto_renew'         => false,
				'licence_expiration' => strtotime( 'next week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2022-05-12' ),
			],
			'pricing'   => $pricing,
		],
		'expected' => [
			'countdown'        => $countdown,
			'message' => 'Renew with a <strong>$9.8 discount</strong> before it is too late, you will only pay <strong>$24.5</strong>!',
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
			'message' => 'Renew with a <strong>$9.8 discount</strong> before it is too late, you will only pay <strong>$24.5</strong>!',

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
				'creation_date'      => strtotime( 'this year' ),
			],
			'pricing'   => $pricing,
		],
		'expected' => [
			'countdown'        => $countdown,
			'message' => 'Renew before it is too late, you will only pay <strong>$69.3</strong>!',
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
			'countdown'        => $countdown,
			'message' => 'Renew with a <strong>$19.8 discount</strong> before it is too late, you will only pay <strong>$49.5</strong>!',
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
	'testShouldReturnDataWhenLicenseInfiniteAndGrandmothered' => [
		'config'   => [
			'user' => [
				'licence_account'    => -1,
				'licence_expired'    => false,
				'auto_renew'         => false,
				'licence_expiration' => strtotime( 'next week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2022-05-12' ),
			],
			'pricing'   => $pricing,
		],
		'expected' => [
			'countdown'        => $countdown,
			'message' => 'Renew with a <strong>$49.8 discount</strong> before it is too late, you will only pay <strong>$124.5</strong>!',
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
				'creation_date'      => strtotime( 'this year' ),
			],
			'pricing'   => $pricing,
		],
		'expected' => [
			'countdown'        => $countdown,
			'message' => 'Renew before it is too late, you will only pay <strong>$174.3</strong>!',
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
			'countdown'        => $countdown,
			'message' => 'Renew with a <strong>$49.8 discount</strong> before it is too late, you will only pay <strong>$124.5</strong>!',
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
];
