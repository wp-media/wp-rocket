<?php

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
	'testShouldReturnDataWhenLicenseExpired' => [
		'config'   => [
			'user'      => [
				'licence_account'    => 1,
				'licence_expired'    => true,
				'licence_expiration' => strtotime( 'last year' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'creation_date'      => strtotime( 'last year' ),
			],
			'transient' => false,
		],
		'expected' => [
			'renewal_url' => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
		],
	],
];
