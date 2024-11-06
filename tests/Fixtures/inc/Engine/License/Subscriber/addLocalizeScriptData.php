<?php

return [
	'testShouldReturnDefaultWhenLicenseIsInfiniteAndNotExpiringSoon' => [
		'config'   => [
			'user'   => json_decode( json_encode( [
				'licence_account'    => -1,
				'licence_expiration' => strtotime( 'next year' ),
				'date_created'      => strtotime( 'last year' ),
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
			'origin_url' => 'https://api.wp-rocket.me',
		],
		'expected' => [],
	],
	'testShouldReturnDefaultWhenLicenseIsExpired' => [
		'config'   => [
			'user'   => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'last week' ),
				'date_created'      => strtotime( 'last year' ),
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
			'origin_url' => 'https://api.wp-rocket.me',
		],
		'expected' => [],
	],
	'testShouldReturnDefaultWhenPromoNotActive' => [
		'config'   => [
			'user'   => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'next year' ),
				'date_created'      => strtotime( 'last year' ),
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
			'origin_url' => 'https://api.wp-rocket.me',
		],
		'expected' => [],
	],
	'testShouldReturnDefaultWhenLicenceBoughtLessThan14daysAgo' => [
		'config'   => [
			'user'   => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'next year' ),
				'date_created'      => strtotime( 'last week' ),
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
			'origin_url' => 'https://api.wp-rocket.me',
		],
		'expected' => [],
	],
	'testShouldReturnUpdatedArrayWhenLicenseIsSoonExpired' => [
		'config'   => [
			'user'   => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'next week' ),
				'date_created'      => strtotime( 'last year' ),
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
			'origin_url' => 'https://api.wp-rocket.me',
		],
		'expected' => [
			'license_expiration' => strtotime( 'next week' ),
		],
	],
	'testShouldReturnUpdatedArrayWhenPromoActive' => [
		'config'   => [
			'user'   => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'next year' ),
				'date_created'      => strtotime( 'last year' ),
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
			'origin_url' => 'https://api.wp-rocket.me',
		],
		'expected' => [
			'promo_end'  => strtotime( 'next week' ),
		],
	],
];
