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
		'title'    => 'WP Rocket',
		'expected' => 'WP Rocket',
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
		'title'    => 'WP Rocket',
		'expected' => 'WP Rocket',
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
		'title'    => 'WP Rocket',
		'expected' => 'WP Rocket',
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
		'title'    => 'WP Rocket',
		'expected' => 'WP Rocket',
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
		'title'    => 'WP Rocket',
		'expected' => 'WP Rocket',
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
		'title'    => 'WP Rocket',
		'expected' => 'WP Rocket',
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
		'title'    => 'WP Rocket',
		'expected' => 'WP Rocket <span class="rocket-promo-bubble">!</span>',
	],
];
