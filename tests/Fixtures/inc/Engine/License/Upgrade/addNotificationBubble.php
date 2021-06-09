<?php

return [
	'testShouldReturnDefaultWhenLicenseIsInfinite' => [
		'config'   => [
			'licence_account'    => -1,
			'licence_expired'    => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
			'transient'          => false,
			'date_created'          => strtotime( 'last year' ),
		],
		'title'    => 'WP Rocket',
		'expected' => 'WP Rocket',
	],
	'testShouldReturnDefaultWhenLicenseIsExpired' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired'    => true,
			'licence_expiration' => strtotime( 'last week' ),
			'promo_active'       => true,
			'transient'          => false,
			'date_created'          => strtotime( 'last year' ),
		],
		'title'    => 'WP Rocket',
		'expected' => 'WP Rocket',
	],
	'testShouldReturnDefaultWhenLicenseIsSoonExpired' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired'    => false,
			'licence_expiration' => strtotime( 'next week' ),
			'promo_active'       => true,
			'transient'          => false,
			'date_created'          => strtotime( 'last year' ),
		],
		'title'    => 'WP Rocket',
		'expected' => 'WP Rocket',
	],
	'testShouldReturnDefaultWhenPromoNotActive' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired'    => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => false,
			'transient'          => false,
			'date_created'          => strtotime( 'last year' ),
		],
		'title'    => 'WP Rocket',
		'expected' => 'WP Rocket',
	],
	'testShouldReturnDefaultWhenPromoSeen' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired'    => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
			'transient'          => 1,
			'date_created'          => strtotime( 'last year' ),
		],
		'title'    => 'WP Rocket',
		'expected' => 'WP Rocket',
	],
	'testShouldReturnBubbleWhenPromoNotSeen' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired'    => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
			'transient'          => false,
			'date_created'      => strtotime( 'last year' ),
		],
		'title'    => 'WP Rocket',
		'expected' => 'WP Rocket <span class="rocket-promo-bubble">!</span>',
	],
	'testShouldReturnDefaultWhenLicenceBoughtLessThan14daysAgo' => [
		'config'   => [
			'licence_account'    => 1,
			'licence_expired'    => false,
			'licence_expiration' => strtotime( 'next year' ),
			'promo_active'       => true,
			'transient'          => false,
			'date_created'      => strtotime( '2 days ago' ),
		],
		'title'    => 'WP Rocket',
		'expected' => 'WP Rocket',
	],
];
