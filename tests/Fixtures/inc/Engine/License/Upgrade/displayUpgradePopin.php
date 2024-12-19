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
				],
			],
		],
	],
];
