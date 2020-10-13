<?php

return [
	'testShouldReturnNullWhenLicenseIsInfinite' => [
		'config'   => [
			'licence_account'    => -1,
			'licence_expired' => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
			'transient'          => false,
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenLicenseIsExpired' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired' => true,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
			'transient'          => false,
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenLicenseIsExpiredSoon' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired' => false,
			'licence_expiration' => strtotime( 'next week' ),
			'promo_active'       => true,
			'transient'          => false,
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenPromoNotActive' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired' => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => false,
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
			'message' => 'Take advantage of %1$s to speed up more websites: get a %2$s off for upgrading your license to Plus or Infinite!'
		],
		'expected' => [
			'name' => 'Halloween',
			'discount_percent' => 20,
			'message' => 'Take advantage of Halloween to speed up more websites: get a 20% off for upgrading your license to Plus or Infinite!',
		],
	],
	'testShouldReturnDataWhenPromoNotSeenAndLicenseBetweenSingleAndPlus' => [
		'config'   => [
			'licence_account'    => 2,
			'licence_expired' => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
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
			'message' => 'Take advantage of %1$s to speed up more websites: get a %2$s off for upgrading your license to Plus or Infinite!'
		],
		'expected' => [
			'name' => 'Halloween',
			'discount_percent' => 20,
			'message' => 'Take advantage of Halloween to speed up more websites: get a 20% off for upgrading your license to Plus or Infinite!',
		],
	],
	'testShouldReturnDataWhenPromoNotSeenAndLicenseIsPlus' => [
		'config'   => [
			'licence_account'    => 3,
			'licence_expired' => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
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
			'message' => 'Take advantage of %1$s to speed up more websites: get a %2$s off for upgrading your license to Infinite!'
		],
		'expected' => [
			'name' => 'Halloween',
			'discount_percent' => 20,
			'message' => 'Take advantage of Halloween to speed up more websites: get a 20% off for upgrading your license to Infinite!',
		],
	],
];
