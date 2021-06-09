<?php

return [
	'testShouldReturnDefaultWhenLicenseIsInfinite' => [
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
			'transient' => false,
		],
		'expected' => false,
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
			'transient' => false,
		],
		'expected' => false,
	],
	'testShouldReturnDefaultWhenLicenseIsSoonExpired' => [
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
			'transient' => false,
		],
		'expected' => false,
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
			'transient' => false,
		],
		'expected' => false,
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
			'transient' => false,
		],
		'expected' => false,
	],
	'testShouldReturnDefaultWhenPromoSeen' => [
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
			'transient' => 1,
		],
		'expected' => 1,
	],
	'testShouldReturnBubbleWhenPromoNotSeen' => [
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
			'transient' => false,
		],
		'expected' =>  1,
	],
];
