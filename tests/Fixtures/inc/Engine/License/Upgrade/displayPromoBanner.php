<?php

return [
	'testShouldReturnNullWhenLicenseIsInfinite' => [
		'config'   => [
			'licence_account'    => -1,
			'licence_expired' => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
			'promo_end'          => strtotime( 'next week' ),
			'transient'          => false,
			'date_created'          => strtotime( 'last year' ),
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenLicenceBoughtLessThan14daysAgo' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired' => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
			'promo_end'          => strtotime( 'next week' ),
			'transient'          => false,
			'date_created'          => strtotime( 'last week' ),
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenLicenseIsExpired' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired' => true,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
			'promo_end'          => strtotime( 'next week' ),
			'transient'          => false,
			'date_created'          => strtotime( 'last year' ),
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenLicenseIsExpiredSoon' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired' => false,
			'licence_expiration' => strtotime( 'next week' ),
			'promo_active'       => true,
			'promo_end'          => strtotime( 'next week' ),
			'transient'          => false,
			'date_created'          => strtotime( 'last year' ),
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenPromoNotActive' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired' => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => false,
			'promo_end'          => strtotime( 'last week' ),
			'date_created'          => strtotime( 'last year' ),
			'transient'          => false,
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenPromoSeen' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired' => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
			'promo_end'          => strtotime( 'next week' ),
			'date_created'          => strtotime( 'last year' ),
			'transient'          => true,
		],
		'expected' => null,
	],
	'testShouldReturnDataWhenPromoNotSeenAndLicenseSingle' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired' => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
			'promo_end'          => strtotime( 'next hour' ),
			'date_created'          => strtotime( 'last year' ),
			'transient'          => false,
			'promo_data'         => json_decode( json_encode( [
				'name' => 'Halloween',
				'discount_percent' => 20,
			] ) ),
			'pricing'            => [
				'single'   => [
					'websites' => 1,
				],
				'plus'     => [
					'websites' => 3,
				],
			],
			'message' => 'Take advantage of %1$s to speed up more websites:%2$s get a %3$s%4$s off%5$s for %3$supgrading your license to Plus or Infinite!%5$s'
		],
		'expected' => [
			'name' => 'Halloween',
			'discount_percent' => 20,
			'message' => 'Take advantage of Halloween to speed up more websites:<br> get a <strong>20% off</strong> for <strong>upgrading your license to Plus or Infinite!</strong>',
		],
	],
	'testShouldReturnDataWhenPromoNotSeenAndLicenseBetweenSingleAndPlus' => [
		'config'   => [
			'licence_account'    => 2,
			'licence_expired' => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
			'promo_end'          => strtotime( 'next hour' ),
			'date_created'          => strtotime( 'last year' ),
			'transient'          => false,
			'promo_data'         => json_decode( json_encode( [
				'name' => 'Halloween',
				'discount_percent' => 20,
			] ) ),
			'pricing'            => [
				'single'   => [
					'websites' => 1,
				],
				'plus'     => [
					'websites' => 3,
				],
			],
			'message' => 'Take advantage of %1$s to speed up more websites:%2$s get a %3$s%4$s off%5$s for %3$supgrading your license to Plus or Infinite!%5$s'
		],
		'expected' => [
			'name' => 'Halloween',
			'discount_percent' => 20,
			'message' => 'Take advantage of Halloween to speed up more websites:<br> get a <strong>20% off</strong> for <strong>upgrading your license to Plus or Infinite!</strong>',
		],
	],
	'testShouldReturnDataWhenPromoNotSeenAndLicenseIsPlus' => [
		'config'   => [
			'licence_account'    => 3,
			'licence_expired' => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
			'promo_end'          => strtotime( 'next hour' ),
			'date_created'          => strtotime( 'last year' ),
			'transient'          => false,
			'promo_data'         => json_decode( json_encode( [
				'name' => 'Halloween',
				'discount_percent' => 20,
			] ) ),
			'pricing'            => [
				'single'   => [
					'websites' => 1,
				],
				'plus'     => [
					'websites' => 3,
				],
			],
			'message' => 'Take advantage of %1$s to speed up more websites:%2$s get a %3$s%4$s off%5$s for %3$supgrading your license to Infinite!%5$s'
		],
		'expected' => [
			'name' => 'Halloween',
			'discount_percent' => 20,
			'message' => 'Take advantage of Halloween to speed up more websites:<br> get a <strong>20% off</strong> for <strong>upgrading your license to Infinite!</strong>',
		],
	],
];
