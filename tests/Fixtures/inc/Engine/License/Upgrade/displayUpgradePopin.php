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
			'upgrades' => [
				(object) [
					"name"=> "Growth",
				]
			],
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenUserIsRevoked' => [
		'config'   => [
			'license_account'    => 1,
			'licence_expiration' => false,
			'is_revoked'          => true,
			'promo_active' => true,
			'upgrades' => [
				(object) [
					'name' => 'Growth',
					'slug' => 'growth',
					'saving' => "40",
					'upgrade_url' => "https://growthupgradeurl.com/",
					'regular_price' => "50",
					'websites' => "3",
					'stack' => false,
				]
			],
		],
		'expected' => null,
	],
	'testShouldDisplayPopInWhenLicenseIsSingle' => [
		'config'   => [
			'license_account'    => 1,
			'licence_expiration' => false,
			'promo_active' => false,
			'upgrades' => [
				(object) [
					'name' => 'Growth',
					'slug' => 'growth',
					'saving' => "x",
					'upgrade_url' => "x",
					'regular_price' => "x",
					'websites' => "x",
					'stacked' => false,
				]
			],
		],
		'expected' => [
			'is_promo_active' => false,
			'upgrades' => [
				'growth' => [
					'name' => 'Growth',
					'price' => 'x',
					'websites' => 'x',
					'upgrade_url' => 'x',
					'currency' => 'USD',
					'currency_symbol' => '$',
				],
			],
		],
	],
	'testShouldDisplayPopInWhenLicenseIsSingleAndCurrencyEuro' => [
		'config'   => [
			'currency' => 'EUR',
			'license_account'    => 1,
			'licence_expiration' => false,
			'promo_active' => false,
			'upgrades' => [
				(object) [
					'name' => 'Growth',
					'slug' => 'growth',
					'saving' => "x",
					'upgrade_url' => "x",
					'regular_price' => "x",
					'websites' => "x",
					'stacked' => false,
				]
			],
		],
		'expected' => [
			'is_promo_active' => false,
			'upgrades' => [
				'growth' => [
					'name' => 'Growth',
					'price' => 'x',
					'websites' => 'x',
					'upgrade_url' => 'x',
					'currency' => 'EUR',
					'currency_symbol' => '€',
				],
			],
		],
	],

	'testShouldDisplayPopInWithPromoWhenLicenseIsSingle' => [
		'config'   => [
			'license_account'    => 1,
			'licence_expiration' => false,
			'promo_active' => true,
			'upgrades' => [
				(object) [
					'name' => 'Growth',
					'slug' => 'growth',
					'saving' => "40",
					'upgrade_url' => "x",
					'regular_price' => "50",
					'websites' => "x",
					'stacked' => false,
				]
			],
		],
		'expected' => [
			'is_promo_active' => true,
			'upgrades' => [
				'growth' => [
					'name' => 'Growth',
					'price' => '40',
					'websites' => 'x',
					'upgrade_url' => 'x',
					'saving' => '10',
					'regular_price' => "50",
					'currency' => 'USD',
					'currency_symbol' => '$',
				],
			],
		],
	],
	'testShouldDisplayPopInWithPromoWhenLicenseIsSingleWithEuro' => [
		'config'   => [
			'currency' => 'EUR',
			'license_account'    => 1,
			'licence_expiration' => false,
			'promo_active' => true,
			'upgrades' => [
				(object) [
					'name' => 'Growth',
					'slug' => 'growth',
					'saving' => "40",
					'upgrade_url' => "x",
					'regular_price' => "50",
					'websites' => "x",
					'stacked' => false,
				]
			],
		],
		'expected' => [
			'is_promo_active' => true,
			'upgrades' => [
				'growth' => [
					'name' => 'Growth',
					'price' => '40',
					'websites' => 'x',
					'upgrade_url' => 'x',
					'saving' => '10',
					'regular_price' => "50",
					'currency' => 'EUR',
					'currency_symbol' => '€',
				],
			],
		],
	],

	'testShouldDisplayPopInWhenLicenseIsBetweenSingleAndPlus' => [
		'config'   => [
			'license_account'    => 2,
			'licence_expiration' => false,
			'promo_active' => false,
			'upgrades' => [
				(object) [
					'name' => 'Growth',
					'slug' => 'growth',
					'saving' => "x",
					'upgrade_url' => "x",
					'regular_price' => "x",
					'websites' => "x",
					'stacked' => false,
				]
			],
		],
		'expected' => [
			'is_promo_active' => false,
			'upgrades' => [
				'growth' => [
					'name' => 'Growth',
					'price' => 'x',
					'websites' => 'x',
					'upgrade_url' => 'x',
					'currency' => 'USD',
					'currency_symbol' => '$',
				],
			],
		],
	],
	'testShouldDisplayPopInWhenLicenseIsBetweenSingleAndPlusWithEuro' => [
		'config'   => [
			'currency' => 'EUR',
			'license_account'    => 2,
			'licence_expiration' => false,
			'promo_active' => false,
			'upgrades' => [
				(object) [
					'name' => 'Growth',
					'slug' => 'growth',
					'saving' => "x",
					'upgrade_url' => "x",
					'regular_price' => "x",
					'websites' => "x",
					'stacked' => false,
				]
			],
		],
		'expected' => [
			'is_promo_active' => false,
			'upgrades' => [
				'growth' => [
					'name' => 'Growth',
					'price' => 'x',
					'websites' => 'x',
					'upgrade_url' => 'x',
					'currency' => 'EUR',
					'currency_symbol' => '€',
				],
			],
		],
	],

	'testShouldDisplayPopInWithPromoWhenLicenseIsBetweenSingleAndPlus' => [
		'config'   => [
			'license_account'    => 2,
			'licence_expiration' => false,
			'promo_active' => true,
			'upgrades' => [
				(object) [
					'name' => 'Growth',
					'slug' => 'growth',
					'saving' => "40",
					'upgrade_url' => "x",
					'regular_price' => "50",
					'websites' => "x",
					'stacked' => false,
				]
			],
		],
		'expected' => [
			'is_promo_active' => true,
			'upgrades' => [
				'growth' => [
					'name' => 'Growth',
					'price' => '40',
					'websites' => 'x',
					'upgrade_url' => 'x',
					'saving' => '10',
					'regular_price' => "50",
					'currency' => 'USD',
					'currency_symbol' => '$',
				],
			],
		],
	],
	'testShouldDisplayPopInWithPromoWhenLicenseIsBetweenSingleAndPlusWithEuro' => [
		'config'   => [
			'currency' => 'EUR',
			'license_account'    => 2,
			'licence_expiration' => false,
			'promo_active' => true,
			'upgrades' => [
				(object) [
					'name' => 'Growth',
					'slug' => 'growth',
					'saving' => "40",
					'upgrade_url' => "x",
					'regular_price' => "50",
					'websites' => "x",
					'stacked' => false,
				]
			],
		],
		'expected' => [
			'is_promo_active' => true,
			'upgrades' => [
				'growth' => [
					'name' => 'Growth',
					'price' => '40',
					'websites' => 'x',
					'upgrade_url' => 'x',
					'saving' => '10',
					'regular_price' => "50",
					'currency' => 'EUR',
					'currency_symbol' => '€',
				],
			],
		],
	],

	'testShouldDisplayPopInWhenLicenseIsPlus' => [
		'config'   => [
			'license_account'    => 3,
			'licence_expiration' => false,
			'promo_active' => false,
			'upgrades' => [
				(object) [
					'name' => 'Growth',
					'slug' => 'growth',
					'saving' => "x",
					'upgrade_url' => "x",
					'regular_price' => "x",
					'websites' => "x",
					'stacked' => false,
				]
			],
		],
		'expected' => [
			'is_promo_active' => false,
			'upgrades' => [
				'growth' => [
					'name' => 'Growth',
					'price' => 'x',
					'websites' => 'x',
					'upgrade_url' => 'x',
					'currency' => 'USD',
					'currency_symbol' => '$',
				],
			],
		],
	],
	'testShouldDisplayPopInWhenLicenseIsPlusWithEuro' => [
		'config'   => [
			'currency' => 'EUR',
			'license_account'    => 3,
			'licence_expiration' => false,
			'promo_active' => false,
			'upgrades' => [
				(object) [
					'name' => 'Growth',
					'slug' => 'growth',
					'saving' => "x",
					'upgrade_url' => "x",
					'regular_price' => "x",
					'websites' => "x",
					'stacked' => false,
				]
			],
		],
		'expected' => [
			'is_promo_active' => false,
			'upgrades' => [
				'growth' => [
					'name' => 'Growth',
					'price' => 'x',
					'websites' => 'x',
					'upgrade_url' => 'x',
					'currency' => 'EUR',
					'currency_symbol' => '€',
				],
			],
		],
	],

	'testShouldDisplayPopInWithPromoWhenLicenseIsPlus' => [
		'config'   => [
			'license_account'    => 3,
			'licence_expiration' => false,
			'promo_active' => true,
			'upgrades' => [
				(object) [
					'name' => 'Growth',
					'slug' => 'growth',
					'saving' => "40",
					'upgrade_url' => "x",
					'regular_price' => "50",
					'websites' => "x",
					'stacked' => false,
				]
			],
		],
		'expected' => [
			'is_promo_active' => true,
			'upgrades' => [
				'growth' => [
					'name' => 'Growth',
					'price' => '40',
					'websites' => 'x',
					'upgrade_url' => 'x',
					'saving' => '10',
					'regular_price' => "50",
					'currency' => 'USD',
					'currency_symbol' => '$',
				],
			],
		],
	],
	'testShouldDisplayPopInWithPromoWhenLicenseIsPlusWithEuro' => [
		'config'   => [
			'currency' => 'EUR',
			'license_account'    => 3,
			'licence_expiration' => false,
			'promo_active' => true,
			'upgrades' => [
				(object) [
					'name' => 'Growth',
					'slug' => 'growth',
					'saving' => "40",
					'upgrade_url' => "x",
					'regular_price' => "50",
					'websites' => "x",
					'stacked' => false,
				]
			],
		],
		'expected' => [
			'is_promo_active' => true,
			'upgrades' => [
				'growth' => [
					'name' => 'Growth',
					'price' => '40',
					'websites' => 'x',
					'upgrade_url' => 'x',
					'saving' => '10',
					'regular_price' => "50",
					'currency' => 'EUR',
					'currency_symbol' => '€',
				],
			],
		],
	],
];
