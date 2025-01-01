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
			'upgrades' => [],
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
			'message' => 'Take advantage of %1$s to speed up more websites:%2$s get a %3$s%4$s off%5$s for %3$supgrading your license to %6$s!%5$s',
			'upgrades' => [
				(object) [
					'name' => 'Growth',
					'slug' => 'growth',
					'saving' => 150,
					'upgrade_url' => "https://growthupgradeurl.com/",
					'regular_price' => 200,
					'websites' => "3",
					'stacked' => false,
				]
			],
		],
		'expected' => [
			'name' => 'Halloween',
			'discount_percent' => 20,
			'message' => 'Take advantage of Halloween to speed up more websites:<br> get a <strong>20% off</strong> for <strong>upgrading your license to Growth!</strong>',
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
			'message' => 'Take advantage of %1$s to speed up more websites:%2$s get a %3$s%4$s off%5$s for %3$supgrading your license to %6$s!%5$s',
			'upgrades' => [
				(object) [
					'name' => 'Multi',
					'slug' => 'multi100',
					'saving' => 100,
					'upgrade_url' => "https://growthupgradeurl.com/",
					'regular_price' => 150,
					'websites' => "3",
					'stacked' => false,
				]
			],
		],
		'expected' => [
			'name' => 'Halloween',
			'discount_percent' => 20,
			'message' => 'Take advantage of Halloween to speed up more websites:<br> get a <strong>20% off</strong> for <strong>upgrading your license to Multi!</strong>',
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
			'message' => 'Take advantage of %1$s to speed up more websites:%2$s get a %3$s%4$s off%5$s for %3$supgrading your license to %6$s!%5$s',
			'upgrades' => [
				(object) [
					'name' => 'Growth',
					'slug' => 'growth',
					'saving' => 200,
					'upgrade_url' => "https://growthupgradeurl.com/",
					'regular_price' => 250,
					'websites' => "3",
					'stacked' => false,
				]
			],
		],
		'expected' => [
			'name' => 'Halloween',
			'discount_percent' => 20,
			'message' => 'Take advantage of Halloween to speed up more websites:<br> get a <strong>20% off</strong> for <strong>upgrading your license to Growth!</strong>',
		],
	],
];
