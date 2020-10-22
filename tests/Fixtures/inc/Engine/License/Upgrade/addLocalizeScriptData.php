<?php

return [
	'testShouldReturnDefaultWhenLicenseIsInfinite' => [
		'config'   => [
			'licence_account'    => -1,
			'licence_expired'    => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
			'promo_end'          => strtotime( 'next week' ),
		],
		'data'    => [
			'nonce'      => 12345,
			'origin_url' => 'https://wp-rocket.me',
		],
		'expected' => [
			'nonce'      => 12345,
			'origin_url' => 'https://wp-rocket.me',
		],
	],
	'testShouldReturnDefaultWhenLicenseIsExpired' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired'    => true,
			'licence_expiration' => strtotime( 'last week' ),
			'promo_active'       => true,
			'promo_end'          => strtotime( 'next week' ),
		],
		'data'    => [
			'nonce'      => 12345,
			'origin_url' => 'https://wp-rocket.me',
		],
		'expected' => [
			'nonce'      => 12345,
			'origin_url' => 'https://wp-rocket.me',
		],
	],
	'testShouldReturnDefaultWhenLicenseIsSoonExpired' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired'    => false,
			'licence_expiration' => strtotime( 'next week' ),
			'promo_active'       => true,
			'promo_end'          => strtotime( 'next week' ),
		],
		'data'    => [
			'nonce'      => 12345,
			'origin_url' => 'https://wp-rocket.me',
		],
		'expected' => [
			'nonce'      => 12345,
			'origin_url' => 'https://wp-rocket.me',
		],
	],
	'testShouldReturnDefaultWhenPromoNotActive' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired'    => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => false,
			'promo_end'          => strtotime( 'last week' ),
		],
		'data'    => [
			'nonce'      => 12345,
			'origin_url' => 'https://wp-rocket.me',
		],
		'expected' => [
			'nonce'      => 12345,
			'origin_url' => 'https://wp-rocket.me',
		],
	],
	'testShouldReturnUpdatedArrayWhenPromoActive' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired'    => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
			'promo_end'          => strtotime( 'next week' ),
		],
		'data'    => [
			'nonce'      => 12345,
			'origin_url' => 'https://wp-rocket.me',
		],
		'expected' => [
			'nonce'      => 12345,
			'origin_url' => 'https://wp-rocket.me',
			'promo_end'  => strtotime( 'next week' ),
		],
	],
];
