<?php

return [
	'testShouldDoNothingWhenLicenseIsInfinite' => [
		'config'   => [
			'licence_account'    => -1,
			'licence_expired'    => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
			'transient'          => false,
			'date_created'          => strtotime( 'last year' ),
		],
		'expected' => false,
	],
	'testShouldDoNothingWhenLicenseIsExpired' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired'    => true,
			'licence_expiration' => strtotime( 'last year' ),
			'promo_active'       => true,
			'transient'          => false,
			'date_created'          => strtotime( 'last year' ),
		],
		'expected' => false,
	],
	'testShouldDoNothingWhenLicenseIsSoonExpired' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired'    => false,
			'licence_expiration' => strtotime( 'next week' ),
			'promo_active'       => true,
			'transient'          => false,
			'date_created'          => strtotime( 'last year' ),
		],
		'expected' => false,
	],
	'testShouldDoNothingWhenPromoNotActive' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired'    => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => false,
			'transient'          => false,
			'date_created'          => strtotime( 'last year' ),
		],
		'expected' => false,
	],
	'testShouldDoNothingWhenPromoSeen' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired'    => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
			'transient'          => 1,
			'date_created'          => strtotime( 'last year' ),
		],
		'expected' => false,
	],
	'testShouldSetTransientWhenPromoNotSeen' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired'    => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
			'transient'          => false,
			'date_created'          => strtotime( 'last year' ),
		],
		'expected' => true,
	],
];
