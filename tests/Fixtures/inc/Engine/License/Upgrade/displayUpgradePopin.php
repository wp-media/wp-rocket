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
					'websites'    => 3,
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me /d89e18ee/plus/',
				],
				'infinite' => [
					'price'       => 200,
					'websites'    => -1,
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me /d89e18ee/infinite/',
				],
			],
		],
		'expected' => [
			'upgrades' => [
				'plus' => [
					'name'        => 'Plus',
					'price'       => 50,
					'websites'    => 3,
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me /d89e18ee/plus/',
				],
				'infinite' => [
					'name'        => 'Infinite',
					'price'       => 200,
					'websites'    => -1,
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me /d89e18ee/infinite/',
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
					'websites'    => 3,
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me /d89e18ee/plus/',
				],
				'infinite' => [
					'price'       => 150,
					'websites'    => -1,
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me /d89e18ee/infinite/',
				],
			],
		],
		'expected' => [
			'upgrades' => [
				'infinite' => [
					'name'        => 'Infinite',
					'price'       => 150,
					'websites'    => -1,
					'upgrade_url' => 'https://wp-rocket.me/checkout/upgrade/roger@wp-rocket.me /d89e18ee/infinite/',
				],
			],
		],
	],
];
