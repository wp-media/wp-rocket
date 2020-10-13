<?php

return [
	'testShouldDoNothingWhenLicenseIsInfinite' => [
		'config'   => [
			'licence_account'    => -1,
			'licence_expired'    => false,
			'promo_active'       => true,
			'transient'          => false,
		],
		'expected' => false,
	],
	'testShouldDoNothingWhenLicenseIsExpired' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired'    => true,
			'promo_active'       => true,
			'transient'          => false,
		],
		'expected' => false,
	],
	'testShouldDoNothingWhenPromoNotActive' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired'    => false,
			'promo_active'       => false,
			'transient'          => false,
		],
		'expected' => false,
	],
	'testShouldDoNothingWhenPromoSeen' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired'    => false,
			'promo_active'       => true,
			'transient'          => 1,
		],
		'expected' => false,
	],
	'testShouldSetTransientWhenPromoNotSeen' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired'    => false,
			'promo_active'       => true,
			'transient'          => false,
		],
		'expected' => true,
	],
];
