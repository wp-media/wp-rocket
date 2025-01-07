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
	'testShouldDisplaySectionWhenLicenseIsNotExpiredAndNotInfinite' => [
		'config'   => [
			'license_account'    => 1,
			'licence_expiration' => false,
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
		'expected' => '',
	],
];
