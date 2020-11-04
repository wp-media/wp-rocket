<?php

return [
	'testShouldReturnDefaultWhenLicenseIsInfiniteAndNotExpiringSoon' => [
		'config'   => [
			'user'   => json_decode( json_encode( [
				'licence_account'    => -1,
				'licence_expiration' => strtotime( 'next year' ),
			] ) ),
			'pricing' => json_decode( json_encode( [
				'promo' => [
					'start_date' => strtotime( 'last week' ),
					'end_date'   => strtotime( 'next week' ),
				],
			] ) ),
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
			'user'   => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'last week' ),
			] ) ),
			'pricing' => json_decode( json_encode( [
				'promo' => [
					'start_date' => strtotime( 'last week' ),
					'end_date'   => strtotime( 'next week' ),
				],
			] ) ),
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
			'user'   => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'next year' ),
			] ) ),
			'pricing' => json_decode( json_encode( [
				'promo' => [
					'start_date' => strtotime( 'last month' ),
					'end_date'   => strtotime( 'last week' ),
				],
			] ) ),
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
	'testShouldReturnUpdatedArrayWhenLicenseIsSoonExpired' => [
		'config'   => [
			'user'   => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'next week' ),
			] ) ),
			'pricing' => json_decode( json_encode( [
				'promo' => [
					'start_date' => strtotime( 'last week' ),
					'end_date'   => strtotime( 'next week' ),
				],
			] ) ),
		],
		'data'    => [
			'nonce'      => 12345,
			'origin_url' => 'https://wp-rocket.me',
		],
		'expected' => [
			'nonce'              => 12345,
			'origin_url'         => 'https://wp-rocket.me',
			'license_expiration' => strtotime( 'next week' ),
		],
	],
	'testShouldReturnUpdatedArrayWhenPromoActive' => [
		'config'   => [
			'user'   => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'next year' ),
			] ) ),
			'pricing' => json_decode( json_encode( [
				'promo' => [
					'start_date' => strtotime( 'last week' ),
					'end_date'   => strtotime( 'next week' ),
				],
			] ) ),
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
