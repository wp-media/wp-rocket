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
		'grandmother_date'=> 315532800,
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

// Pricing endpoint unreachable: no renewal price available for the license.
$pricing_unavailable           = $pricing;
$pricing_unavailable['single'] = json_decode( json_encode( [ 'websites' => 1 ] ) );

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
	'testShouldReturnNullWhenLicenseIsRevoked' => [
		'config'   => [
			'user' => [
				'licence_account'    => 1,
				'licence_expired'    => false,
				'auto_renew'         => false,
				'licence_expiration' => strtotime( 'next week' ),
				'is_revoked'			 => true,
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
			'message' => 'Renew now at <strong>$34.3 <span class="renewal-tax__info">(excl. taxes)</span></strong> and keep your site optimized.',
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
			'more_info' => true
		],
	],
	'testShouldReturnDataWhenLicenseAndSingleAndNotGrandfatheredWithEuro' => [
		'config'   => [
			'user' => [
				'currency' => 'EUR',
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
			'message' => 'Renew now at <strong>34.3€ <span class="renewal-tax__info">(excl. taxes)</span></strong> and keep your site optimized.',
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
			'more_info' => true
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
			'message' => 'Renew now at <strong>$34.3 <span class="renewal-tax__info">(excl. taxes)</span></strong> and keep your site optimized.',
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
			'more_info' => true
		],
	],
	'testShouldReturnDataWhenLicenseAndSingleAndGrandmotheredWithEuro' => [
		'config'   => [
			'user' => [
				'currency' => 'EUR',
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
			'message' => 'Renew now at <strong>34.3€ <span class="renewal-tax__info">(excl. taxes)</span></strong> and keep your site optimized.',
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
			'more_info' => true
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
			'message' => 'Renew now at <strong>$24.5 <span class="renewal-tax__info">(excl. taxes)</span></strong> and keep your site optimized.',
			'more_info' => true,
			'countdown' => $countdown,
			'renewal_url' => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
	'testShouldReturnDataWhenLicenseSingleAndGrandfatheredWithEuro' => [
		'config'   => [
			'user' => [
				'currency' => 'EUR',
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
			'message' => 'Renew now at <strong>24.5€ <span class="renewal-tax__info">(excl. taxes)</span></strong> and keep your site optimized.',
			'more_info' => true,
			'countdown' => $countdown,
			'renewal_url' => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
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
			'more_info' => true,
			'countdown' => $countdown,
			'message' => 'Renew now at <strong>$69.3 <span class="renewal-tax__info">(excl. taxes)</span></strong> and keep your site optimized.',
			'renewal_url' => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
	'testShouldReturnDataWhenLicensePlusAndNotGrandfatheredWithEuro' => [
		'config'   => [
			'user' => [
				'currency' => 'EUR',
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
			'more_info' => true,
			'countdown' => $countdown,
			'message' => 'Renew now at <strong>69.3€ <span class="renewal-tax__info">(excl. taxes)</span></strong> and keep your site optimized.',
			'renewal_url' => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
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
			'message' => 'Renew now at <strong>$49.5 <span class="renewal-tax__info">(excl. taxes)</span></strong> and keep your site optimized.',
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
			'more_info' => true,
		],
	],
	'testShouldReturnDataWhenLicensePlusAndGrandfatheredWithEuro' => [
		'config'   => [
			'user' => [
				'currency' => 'EUR',
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
			'message' => 'Renew now at <strong>49.5€ <span class="renewal-tax__info">(excl. taxes)</span></strong> and keep your site optimized.',
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
			'more_info' => true,
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
			'message' => 'You will soon lose the features that keep your site fast, plus access to updates and support.',
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
			'more_info' => false,
		],
	],
	'testShouldReturnDataWhenLicenseInfiniteAndGrandmotheredWithEuro' => [
		'config'   => [
			'user' => [
				'currency' => 'EUR',
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
			'message' => 'You will soon lose the features that keep your site fast, plus access to updates and support.',
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
			'more_info' => false,
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
			'message' => 'You will soon lose the features that keep your site fast, plus access to updates and support.',
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
			'more_info' => false,
		],
	],
	'testShouldReturnDataWhenLicenseInfiniteAndNotGrandfatheredWithEuro' => [
		'config'   => [
			'user' => [
				'currency' => 'EUR',
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
			'message' => 'You will soon lose the features that keep your site fast, plus access to updates and support.',
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
			'more_info' => false,
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
			'message' => 'You will soon lose the features that keep your site fast, plus access to updates and support.',
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
			'more_info' => false,
		],
	],
	'testShouldReturnNullWhenUserIsTrialCustomer' => [
		'config'   => [
			'user' => [
				'licence_account'    => 1,
				'licence_expired'    => false,
				'auto_renew'         => false,
				'licence_expiration' => strtotime( 'next week' ),
				'is_trial_customer'  => true,
			],
		],
		'expected' => null,
	],

	'testShouldReturnDataWhenLicenseInfiniteAndGrandfatheredWithEuro' => [
		'config'   => [
			'user' => [
				'currency' => 'EUR',
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
			'message' => 'You will soon lose the features that keep your site fast, plus access to updates and support.',
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
			'more_info' => false,
		],
	],
	'testShouldHidePriceLineWhenPricingUnavailableAndNotGrandfathered' => [
		'config'   => [
			'user' => [
				'licence_account'    => 1,
				'licence_expired'    => false,
				'auto_renew'         => false,
				'licence_expiration' => strtotime( 'next week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( 'this year' ),
			],
			'pricing'   => $pricing_unavailable,
		],
		'expected' => [
			'countdown'        => $countdown,
			'message' => 'Renew now at <strong>$0 <span class="renewal-tax__info">(excl. taxes)</span></strong> and keep your site optimized.',
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
			'more_info' => false,
		],
	],
	'testShouldHidePriceLineWhenPricingUnavailableAndGrandfathered' => [
		'config'   => [
			'user' => [
				'licence_account'    => 1,
				'licence_expired'    => false,
				'auto_renew'         => false,
				'licence_expiration' => strtotime( 'next week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2021-01-10' ),
			],
			'pricing'   => $pricing_unavailable,
		],
		'expected' => [
			'countdown'        => $countdown,
			'message' => 'Renew now at <strong>$0 <span class="renewal-tax__info">(excl. taxes)</span></strong> and keep your site optimized.',
			'renewal_url'      => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
			'more_info' => false,
		],
	],

];
