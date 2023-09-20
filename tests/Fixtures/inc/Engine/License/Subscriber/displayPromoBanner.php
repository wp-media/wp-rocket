<?php

return [
	'testShouldReturnNullWhenLicenseIsInfinite' => [
		'config' => [
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
		'expected' => '',
	],
	'testShouldReturnNullWhenLicenseIsExpired' => [
		'config' => [
			'user'   => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'last month' ),
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
		'expected' => '',
	],
	'testShouldReturnNullWhenLicenseIsSoonExpired' => [
		'config' => [
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
		'expected' => '',
	],
	'testShouldReturnNullWhenPromoNotActive' => [
		'config' => [
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
		'expected' => '',
	],
	'testShouldReturnNullWhenPromoDismissed' => [
		'config' => [
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
		'expected' => '',
	],
	'testShouldReturnNullWhenLicenceBoughtLessThan14daysAgo' => [
		'config' => [
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
		'expected' => '',
	],
	'testShouldDisplayBannerForSingleWhenPromoNotDismissed' => [
		'config' => [
			'user'   => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'next year' ),
				'date_created'      => strtotime( 'last year' ),
			] ) ),
			'pricing' => json_decode( json_encode( [
				'licenses' => [
					'single'   => [
						'websites' => 1,
					],
					'plus'     => [
						'prices'      => [
							'from_single' => [
								'regular' => 50,
							],
						],
						'websites'    => 3,

					],
					'infinite' => [
						'prices'       => [
							'from_single' => [
								'regular' => 200,
							],
						],
						'websites'    => -1,
					],
				],
				'promo' => [
					'name' => 'Halloween',
					'discount_percent' => 20,
					'start_date' => strtotime( 'last week' ),
					'end_date' => strtotime( 'next week' ),
				],
			] ) ),
			'transient' => false,
		],
		'expected' => '<div class="rocket-promo-banner" id="rocket-promo-banner">
		<div>
		<h3 class="rocket-promo-title">
		<span class="rocket-promo-discount">
		20% off</span>
		Halloween promotion is live!</h3>
		<p class="rocket-promo-message">
		Take advantage of Halloween to speed up more websites:<br>
		get a<strong>
		20% off</strong>
		for<strong>
		upgrading your license to Plus or Infinite!</strong>
		</p>
		</div>
		<div class="rocket-promo-cta-block">
		<p class="rocket-promo-deal">
		Hurry Up! Deal ends in:</p>
		<ul class="rocket-promo-countdown" id="rocket-promo-countdown">
		<li class="rocket-countdown-item">
		<span class="rocket-countdown-value rocket-countdown-days">
		0</span>
		Days</li>
		<li class="rocket-countdown-item">
		<span class="rocket-countdown-value rocket-countdown-hours">
		0</span>
		Hours</li>
		<li class="rocket-countdown-item">
		<span class="rocket-countdown-value rocket-countdown-minutes">
		0</span>
		Minutes</li>
		<li class="rocket-countdown-item">
		<span class="rocket-countdown-value rocket-countdown-seconds">
		0</span>
		Seconds</li>
		</ul>
		<button class="rocket-promo-cta wpr-popin-upgrade-toggle">
		Upgrade now</button>
		</div>
		<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-promotion">
		<span class="screen-reader-text">
		Dismiss this notice</span>
		</button>
		</div>',
	],
	'testShouldDisplayBannerForPlusWhenPromoNotDismissed' => [
		'config' => [
			'user'   => json_decode( json_encode( [
				'licence_account'    => 3,
				'licence_expiration' => strtotime( 'next year' ),
				'date_created'      => strtotime( 'last year' ),
			] ) ),
			'pricing' => json_decode( json_encode( [
				'licenses' => [
					'single'   => [
						'websites' => 1,
					],
					'plus'     => [
						'prices'      => [
							'from_single' => [
								'regular' => 50,
							],
						],
						'websites'    => 3,

					],
					'infinite' => [
						'prices'       => [
							'from_single' => [
								'regular' => 200,
							],
						],
						'websites'    => -1,
					],
				],
				'promo' => [
					'name' => 'Halloween',
					'discount_percent' => 20,
					'start_date' => strtotime( 'last week' ),
					'end_date' => strtotime( 'next week' ),
				],
			] ) ),
			'transient' => false,
		],
		'expected' => '<div class="rocket-promo-banner" id="rocket-promo-banner">
		<div>
		<h3 class="rocket-promo-title">
		<span class="rocket-promo-discount">
		20% off</span>
		Halloween promotion is live!</h3>
		<p class="rocket-promo-message">
		Take advantage of Halloween to speed up more websites:<br>
		get a<strong>
		20% off</strong>
		for<strong>
		upgrading your license to Infinite!</strong>
		</p>
		</div>
		<div class="rocket-promo-cta-block">
		<p class="rocket-promo-deal">
		Hurry Up! Deal ends in:</p>
		<ul class="rocket-promo-countdown" id="rocket-promo-countdown">
		<li class="rocket-countdown-item">
		<span class="rocket-countdown-value rocket-countdown-days">
		0</span>
		Days</li>
		<li class="rocket-countdown-item">
		<span class="rocket-countdown-value rocket-countdown-hours">
		0</span>
		Hours</li>
		<li class="rocket-countdown-item">
		<span class="rocket-countdown-value rocket-countdown-minutes">
		0</span>
		Minutes</li>
		<li class="rocket-countdown-item">
		<span class="rocket-countdown-value rocket-countdown-seconds">
		0</span>
		Seconds</li>
		</ul>
		<button class="rocket-promo-cta wpr-popin-upgrade-toggle">
		Upgrade now</button>
		</div>
		<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-promotion">
		<span class="screen-reader-text">
		Dismiss this notice</span>
		</button>
		</div>',
	],
];
