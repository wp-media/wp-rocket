<?php

return [
	'testShouldReturnNullWhenLicenseIsInfinite' => [
		'config'   => [
			'license_account'    => -1,
			'licence_expiration' => false,
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenLicenseIsExpired' => [
		'config'   => [
			'license_account'    => 1,
			'licence_expiration' => true,
		],
		'expected' => null,
	],
	'testShouldDisplayPopInWhenLicenseIsSingle' => [
		'config'   => [
			'license_account'    => 1,
			'licence_expiration' => false,
			'pricing'            => [
				'single'   => [
					'websites' => 1,
				],
				'plus'     => [
					'price'       => 50,
					'regular'     => 50,
					'websites'    => 3,
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/plus/',
				],
				'infinite' => [
					'price'       => 200,
					'regular'     => 200,
					'websites'    => 'Unlimited',
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/infinite/',
				],
			],
			'promo_active' => false,
		],
		'expected' => [
			'is_promo_active' => false,
			'upgrades' => [
				'plus' => [
					'name'        => 'Plus',
					'price'       => 50,
					'websites'    => 3,
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/plus/',
				],
				'infinite' => [
					'name'        => 'Infinite',
					'price'       => 200,
					'websites'    => 'Unlimited',
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/infinite/',
				],
			],
		],
	],
	'testShouldDisplayPopInWithPromoWhenLicenseIsSingle' => [
		'config'   => [
			'license_account'    => 1,
			'licence_expiration' => false,
			'pricing'            => [
				'single'   => [
					'websites' => 1,
				],
				'plus'     => [
					'price'       => 40,
					'regular'     => 50,
					'websites'    => 3,
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/plus/',
				],
				'infinite' => [
					'price'       => 160,
					'regular'     => 200,
					'websites'    => 'Unlimited',
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/infinite/',
				],
			],
			'promo_active' => true,
		],
		'expected' => [
			'is_promo_active' => true,
			'upgrades' => [
				'plus' => [
					'name'        => 'Plus',
					'price'       => 40,
					'websites'    => 3,
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/plus/',
					'saving'      => 10,
					'regular_price' => 50,
				],
				'infinite' => [
					'name'        => 'Infinite',
					'price'       => 160,
					'websites'    => 'Unlimited',
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/infinite/',
					'saving'      => 40,
					'regular_price' => 200,
				],
			],
		],
	],
	'testShouldDisplayPopInWhenLicenseIsBetweenSingleAndPlus' => [
		'config'   => [
			'license_account'    => 2,
			'licence_expiration' => false,
			'pricing'            => [
				'single'   => [
					'websites' => 1,
				],
				'plus'     => [
					'price'       => 50,
					'regular'     => 50,
					'websites'    => 3,
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/plus/',
				],
				'infinite' => [
					'price'       => 200,
					'regular'     => 200,
					'websites'    => 'Unlimited',
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/infinite/',
				],
			],
			'promo_active' => false,
		],
		'expected' => [
			'is_promo_active' => false,
			'upgrades' => [
				'plus' => [
					'name'        => 'Plus',
					'price'       => 50,
					'websites'    => 3,
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/plus/',
				],
				'infinite' => [
					'name'        => 'Infinite',
					'price'       => 200,
					'websites'    => 'Unlimited',
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/infinite/',
				],
			],
		],
	],
	'testShouldDisplayPopInWithPromoWhenLicenseIsBetweenSingleAndPlus' => [
		'config'   => [
			'license_account'    => 2,
			'licence_expiration' => false,
			'pricing'            => [
				'single'   => [
					'websites' => 1,
				],
				'plus'     => [
					'price'       => 40,
					'regular'     => 50,
					'websites'    => 3,
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/plus/',
				],
				'infinite' => [
					'price'       => 160,
					'regular'     => 200,
					'websites'    => 'Unlimited',
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/infinite/',
				],
			],
			'promo_active' => true,
		],
		'expected' => [
			'is_promo_active' => true,
			'upgrades' => [
				'plus' => [
					'name'        => 'Plus',
					'price'       => 40,
					'websites'    => 3,
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/plus/',
					'saving'      => 10,
					'regular_price' => 50,
				],
				'infinite' => [
					'name'        => 'Infinite',
					'price'       => 160,
					'websites'    => 'Unlimited',
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/infinite/',
					'saving'      => 40,
					'regular_price' => 200,
				],
			],
		],
	],
	'testShouldDisplayPopInWhenLicenseIsPlus' => [
		'config'   => [
			'license_account'    => 3,
			'licence_expiration' => false,
			'pricing'            => [
				'single'   => [
					'websites' => 1,
				],
				'plus'     => [
					'price'       => 50,
					'regular'     => 50,
					'websites'    => 3,
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/plus/',
				],
				'infinite' => [
					'price'       => 150,
					'regular'     => 150,
					'websites'    => 'Unlimited',
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/infinite/',
				],
			],
			'promo_active' => false,
		],
		'expected' => [
			'is_promo_active' => false,
			'upgrades' => [
				'infinite' => [
					'name'        => 'Infinite',
					'price'       => 150,
					'websites'    => 'Unlimited',
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/infinite/',
				],
			],
		],
	],
	'testShouldDisplayPopInWithPromoWhenLicenseIsPlus' => [
		'config'   => [
			'license_account'    => 3,
			'licence_expiration' => false,
			'pricing'            => [
				'single'   => [
					'websites' => 1,
				],
				'plus'     => [
					'price'       => 40,
					'regular'     => 50,
					'websites'    => 3,
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/plus/',
				],
				'infinite' => [
					'price'       => 120,
					'regular'     => 150,
					'websites'    => 'Unlimited',
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/infinite/',
				],
			],
			'promo_active' => true,
		],
		'expected' => [
			'is_promo_active' => true,
			'upgrades' => [
				'infinite' => [
					'name'        => 'Infinite',
					'price'       => 120,
					'websites'    => 'Unlimited',
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me/d89e18ee/infinite/',
					'saving'      => 30,
					'regular_price' => 150,
				],
			],
		],
	],
];
