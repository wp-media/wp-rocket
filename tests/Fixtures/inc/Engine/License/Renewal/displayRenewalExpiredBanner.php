<?php

$pricing = [
	'single'=> json_decode( json_encode( [
		'prices'=> [
			'renewal'=> [
				'is_grandfather'=> 39.2,
				'is_grandmother'=> 49,
				'not_grandfather'=> 59,
				'is_expired'=> 59
			]
		],
		'websites'=> 1
	] ) ),
	'plus'=> json_decode( json_encode( [
		'prices'=> [
			'renewal'=> [
				'is_grandfather'=> 79.2,
				'is_grandmother'=> 99,
				'not_grandfather'=> 119,
				'is_expired'=> 119
			]
		],
		'websites'=> 3
	] ) ),
	'infinite'=> json_decode( json_encode( [
		'prices'=> [
			'renewal'=> [
				'is_grandfather'=> 199.2,
				'is_grandmother'=> 249,
				'not_grandfather'=> 299,
				'is_expired'=> 299
			]
		],
	] ) ),
	'renewals' => json_decode( json_encode( [
		'extra_days'=> 15,
		'grandfather_date'=> 1640995200,
		'grandmother_date'=> 1672531200,
		'discount_percent'=> [
			'is_grandfather' => 20,
			'not_grandfather'=> 0,
			'is_expired'     => 0,
		],
	] ) ),
];

return [
	'shouldReturnNullWhenLicenseIsNotExpired' => [
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
			'disabled_date' => '',
		],
		'expected' => null,
	],
	'shouldReturnNullWhenBannerDismissed' => [
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
			'disabled_date' => '',
		],
		'expected' => null,
	],
	'testShouldReturnDataWhenOCDEnabledAndLicenseExpiredSinceLessThan15DaysAndGrandfathered' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'now - 10 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2021-01-01' ),
				'auto_renew' => false,
			],
			'ocd' => true,
			'transient' => false,
			'pricing' => $pricing,
			'disabled_date' => strtotime( 'now + 5 days' ),
		],
		'expected' => [
			'template' => 'renewal-expired-banner-ocd',
			'data' => [
				'renewal_url'   => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'message' => 'Renew your license for 1 year now and get <strong>$19.8 OFF</strong> immediately: you will only pay <strong>$39.2</strong>!',
				'disabled_date' => strtotime( 'now + 5 days' ),
			],
		],
	],
	'testShouldReturnDataWhenOCDEnabledAndLicenseExpiredSinceLessThan15DaysAndGrandmothered' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'now - 10 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2022-01-10' ),
				'auto_renew' => false,
			],
			'ocd' => true,
			'transient' => false,
			'pricing' => $pricing,
			'disabled_date' => strtotime( 'now + 5 days' ),
		],
		'expected' => [
			'template' => 'renewal-expired-banner-ocd',
			'data' => [
				'renewal_url'   => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'message' => 'Renew your license for 1 year now and get <strong>$10 OFF</strong> immediately: you will only pay <strong>$49</strong>!',
				'disabled_date' => strtotime( 'now + 5 days' ),
			],
		],
	],
	'testShouldReturnDataWhenOCDEnabledAndLicenseExpiredSinceLessThan15DaysAndNotGrandfathered' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'now - 10 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( 'next year' ),
				'auto_renew' => false,
			],
			'ocd' => true,
			'transient' => false,
			'pricing' => $pricing,
			'disabled_date' => strtotime( 'now + 5 days' ),
		],
		'expected' => [
			'template' => 'renewal-expired-banner-ocd',
			'data' => [
				'renewal_url'   => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'message' => 'Renew your license for 1 year now at <strong>$59</strong>.',
				'disabled_date' => strtotime( 'now + 5 days' ),
			],
		],
	],
	'testShouldReturnNullWhenOCDEnabledAndLicenseExpiredSinceLessThan4AndAutoRenewEnabled' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'now - 3 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2021-01-01' ),
				'auto_renew' => true,
			],
			'ocd' => true,
			'transient' => false,
			'pricing' => $pricing,
			'disabled_date' => '',
		],
		'expected' => null,
	],
	'testShouldReturnDataWhenOCDEnabledAndLicenseExpiredSinceLessThan15DaysAndGrandfatheredAndAutoRenewEnabled' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'now - 10 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2021-01-01' ),
				'auto_renew' => true,
			],
			'ocd' => true,
			'transient' => false,
			'pricing' => $pricing,
			'disabled_date' => strtotime( 'now + 5 days' ),
		],
		'expected' => [
			'template' => 'renewal-expired-banner-ocd',
			'data' => [
				'renewal_url'   => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'message' => 'Renew your license for 1 year now and get <strong>$19.8 OFF</strong> immediately: you will only pay <strong>$39.2</strong>!',
				'disabled_date' => strtotime( 'now + 5 days' ),
			],
		],
	],
	'testShouldReturnDataWhenOCDEnabledAndLicenseExpiredSinceLessThan15DaysAndNotGrandfatheredAndAutoRenewEnabled' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'now - 10 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( 'next year' ),
				'auto_renew' => true,
			],
			'ocd' => true,
			'transient' => false,
			'pricing' => $pricing,
			'disabled_date' => strtotime( 'now + 5 days' ),
		],
		'expected' => [
			'template' => 'renewal-expired-banner-ocd',
			'data' => [
				'renewal_url'   => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'message' => 'Renew your license for 1 year now at <strong>$59</strong>.',
				'disabled_date' => strtotime( 'now + 5 days' ),
			],
		],
	],
	'testShouldReturnDataWhenOCDEnabledAndLicenseExpiredSinceMoreThan15Days' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'now - 20 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2021-01-10' ),
				'auto_renew' => true,
			],
			'ocd' => true,
			'transient' => false,
			'pricing' => $pricing,
			'disabled_date' => '',
		],
		'expected' => [
			'template' => 'renewal-expired-banner-ocd-disabled',
			'data' => [
				'renewal_url'   => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'message' => 'Renew your license for 1 year now at <strong>$59</strong>.',
			],
		],
	],
	'testShouldReturnDataWhenOCDEnabledAndLicenseExpiredSinceMoreThan90Days' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'now - 100 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2021-01-10' ),
				'auto_renew' => true,
			],
			'ocd' => true,
			'transient' => false,
			'pricing' => $pricing,
			'disabled_date' => '',
		],
		'expected' => [
			'template' => 'renewal-expired-banner',
			'data' => [
				'renewal_url'   => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'message' => 'Renew your license for 1 year now at <strong>$59</strong>.',
			],
		],
	],
	'testShouldReturnDataWhenOCDDisabledAndLicenseExpiredSinceLessThan15DaysAndGrandfathered' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'now - 10 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2021-01-01' ),
				'auto_renew' => false,
			],
			'ocd' => false,
			'transient' => false,
			'pricing' => $pricing,
			'disabled_date' => '',
		],
		'expected' => [
			'template' => 'renewal-expired-banner',
			'data' => [
				'renewal_url'   => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'message' => 'Renew your license for 1 year now and get <strong>$19.8 OFF</strong> immediately: you will only pay <strong>$39.2</strong>!',
			],
		],
	],
	'testShouldReturnDataWhenOCDDisabledAndLicenseExpiredSinceLessThan15DaysAndGrandmothered' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'now - 10 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2022-01-10' ),
				'auto_renew' => false,
			],
			'ocd' => false,
			'transient' => false,
			'pricing' => $pricing,
			'disabled_date' => '',
		],
		'expected' => [
			'template' => 'renewal-expired-banner',
			'data' => [
				'renewal_url'   => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'message' => 'Renew your license for 1 year now and get <strong>$10 OFF</strong> immediately: you will only pay <strong>$49</strong>!',
			],
		],
	],
	'testShouldReturnDataWhenOCDDisabledAndLicenseExpiredSinceLessThan15DaysAndNotGrandfathered' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'now - 10 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( 'next year' ),
				'auto_renew' => false,
			],
			'ocd' => false,
			'transient' => false,
			'pricing' => $pricing,
			'disabled_date' => '',
		],
		'expected' => [
			'template' => 'renewal-expired-banner',
			'data' => [
				'renewal_url'   => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'message' => 'Renew your license for 1 year now at <strong>$59</strong>.',
			],
		],
	],
	'testShouldReturnDataWhenOCDDisabledAndLicenseExpiredSinceMoreThan15Days' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'now - 20 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2021-12-10' ),
				'auto_renew' => false,
			],
			'ocd' => false,
			'transient' => false,
			'pricing' => $pricing,
			'disabled_date' => '',
		],
		'expected' => [
			'template' => 'renewal-expired-banner',
			'data' => [
				'renewal_url'   => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'message' => 'Renew your license for 1 year now at <strong>$59</strong>.',
			],
		],
	],
	'testShouldReturnNullWhenOCDDisabledAndLicenseExpiredSinceLessThan4AndAutoRenewEnabled' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'now - 3 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2021-01-01' ),
				'auto_renew' => true,
			],
			'ocd' => false,
			'transient' => false,
			'pricing' => $pricing,
			'disabled_date' => '',
		],
		'expected' => null,
	],
	'testShouldReturnDataWhenOCDDisabledAndLicenseExpiredSinceLessThan15DaysAndGrandfatheredAndAutoRenewEnabled' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'now - 10 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2021-01-01' ),
				'auto_renew' => true,
			],
			'ocd' => false,
			'transient' => false,
			'pricing' => $pricing,
			'disabled_date' => '',
		],
		'expected' => [
			'template' => 'renewal-expired-banner',
			'data' => [
				'renewal_url'   => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'message' => 'Renew your license for 1 year now and get <strong>$19.8 OFF</strong> immediately: you will only pay <strong>$39.2</strong>!',
			],
		],
	],
	'testShouldReturnDataWhenOCDDisabledAndLicenseExpiredSinceLessThan15DaysAndGrandmotheredAndAutoRenewEnabled' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'now - 10 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2022-01-10' ),
				'auto_renew' => true,
			],
			'ocd' => false,
			'transient' => false,
			'pricing' => $pricing,
			'disabled_date' => '',
		],
		'expected' => [
			'template' => 'renewal-expired-banner',
			'data' => [
				'renewal_url'   => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'message' => 'Renew your license for 1 year now and get <strong>$10 OFF</strong> immediately: you will only pay <strong>$49</strong>!',
			],
		],
	],
	'testShouldReturnDataWhenOCDDisabledAndLicenseExpiredSinceLessThan15DaysAndNotGrandfatheredAndAutoRenewEnabled' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'now - 10 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( 'next year' ),
				'auto_renew' => true,
			],
			'ocd' => false,
			'transient' => false,
			'pricing' => $pricing,
			'disabled_date' => '',
		],
		'expected' => [
			'template' => 'renewal-expired-banner',
			'data' => [
				'renewal_url'   => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'message' => 'Renew your license for 1 year now at <strong>$59</strong>.',
			],
		],
	],
	'testShouldReturnDataWhenOCDDisabledAndLicenseExpiredSinceMoreThan15DaysAndAutoRenewEnabled' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'now - 20 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( '2021-01-10' ),
				'auto_renew' => true,
			],
			'ocd' => false,
			'transient' => false,
			'pricing' => $pricing,
			'disabled_date' => '',
		],
		'expected' => [
			'template' => 'renewal-expired-banner',
			'data' => [
				'renewal_url'   => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'message' => 'Renew your license for 1 year now at <strong>$59</strong>.',
			],
		],
	],
];
