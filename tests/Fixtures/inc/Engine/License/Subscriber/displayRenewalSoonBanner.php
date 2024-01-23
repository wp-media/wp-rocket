<?php

$pricing = json_decode( json_encode( [
	'licenses' => [
		'single'=> [
			'prices'=> [
				'renewal'=> [
					'is_grandfather'=> 24.5,
					'is_grandmother'=> 24.5,
					'not_grandfather'=> 34.3,
					'is_expired'=> 39.2
				]
			],
			'websites'=> 1
		],
		'plus'=> [
			'prices'=> [
				'renewal'=> [
					'is_grandfather'=> 49.5,
					'is_grandmother'=> 49.5,
					'not_grandfather'=> 69.3,
					'is_expired'=> 79.2
				]
			],
			'websites'=> 3
		],
		'infinite'=> [
			'prices'=> [
				'renewal'=> [
					'is_grandfather'=> 124.5,
					'is_grandmother'=> 124.5,
					'not_grandfather'=> 174.3,
					'is_expired'=> 199.2
				]
			],
		],
	],
	'renewals' => [
		'extra_days'=> 90,
		'grandfather_date'=> 1567296000,
		'grandmother_date'=> 1672389000,
		'discount_percent'=> [
			'is_grandfather' => 20,
			'not_grandfather'=> 0,
			'is_expired'     => 0,
		],
	],
] ) );

return [
	'testShouldReturnNullWhenLicenseIsExpired' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'last year' ),
			] ) ),
			'pricing' => $pricing,
		],
		'expected' => '',
	],
	'testShouldReturnNullWhenLicenseAutoRenew' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'has_auto_renew'     => true,
				'licence_expiration' => strtotime( 'next week' ),
			] ) ),
			'pricing' => $pricing,
		],
		'expected' => '',
	],
	'testShouldReturnNullWhenLicenseNotExpireSoon' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'next year' ),
			] ) ),
			'pricing' => $pricing,
		],
		'expected' => '',
	],
	'testShouldReturnDataWhenLicenseAndSingleAndNotGrandfathered' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'next week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'date_created'      => strtotime( 'last year' ),
			] ) ),
			'pricing'   => $pricing,
		],
		'expected' => '<div class="rocket-renewal-banner">
		<ul class="rocket-promo-countdown" id="rocket-renew-countdown">
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-days">0</span> Days</li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-hours">0</span> Hours</li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-minutes">0</span> Minutes</li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-seconds">0</span> Seconds</li>
		</ul>
		<div class="rocket-renew-message">
			<p>
				Your <strong>WP Rocket license is about to expire</strong>: you will soon lose access to product updates and support.
			</p>
			<p>Renew before it is too late, you will only pay <strong>$34.30</strong>!</p>
		</div>
		<div class="rocket-renew-cta-container">
			<a href="https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer">Renew now</a>
		</div>
	</div>',
	],
	'testShouldReturnDataWhenLicenseSingleAndGrandfathered' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'next week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'date_created'      => strtotime( '2019/08/01' ),
			] ) ),
			'pricing'   => $pricing,
		],
		'expected' => '<div class="rocket-renewal-banner">
		<ul class="rocket-promo-countdown" id="rocket-renew-countdown">
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-days">0</span> Days</li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-hours">0</span> Hours</li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-minutes">0</span> Minutes</li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-seconds">0</span> Seconds</li>
		</ul>
		<div class="rocket-renew-message">
			<p>
				Your <strong>WP Rocket license is about to expire</strong>: you will soon lose access to product updates and support.
			</p>
			<p>
			Renew with a <strong>$9.80 discount</strong> before it is too late, you will only pay <strong>$24.50</strong>!
			</p>
		</div>
		<div class="rocket-renew-cta-container">
			<a href="https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer">Renew now</a>
		</div>
	</div>',
	],
	'testShouldReturnDataWhenLicensePlusAndNotGrandfathered' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 3,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'next week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'date_created'      => strtotime( 'last year' ),
			] ) ),
			'pricing'   => $pricing,
		],
		'expected' => '<div class="rocket-renewal-banner">
		<ul class="rocket-promo-countdown" id="rocket-renew-countdown">
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-days">0</span> Days</li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-hours">0</span> Hours</li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-minutes">0</span> Minutes</li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-seconds">0</span> Seconds</li>
		</ul>
		<div class="rocket-renew-message">
			<p>
				Your <strong>WP Rocket license is about to expire</strong>: you will soon lose access to product updates and support.
			</p>
			<p>Renew before it is too late, you will only pay <strong>$69.30</strong>!</p>
		</div>
		<div class="rocket-renew-cta-container">
			<a href="https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer">Renew now</a>
		</div>
	</div>',
	],
	'testShouldReturnDataWhenLicensePlusAndGrandfathered' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 3,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'next week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'date_created'      => strtotime( '2019/08/01' ),
			] ) ),
			'pricing'   => $pricing,
		],
		'expected' => '<div class="rocket-renewal-banner">
		<ul class="rocket-promo-countdown" id="rocket-renew-countdown">
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-days">0</span> Days</li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-hours">0</span> Hours</li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-minutes">0</span> Minutes</li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-seconds">0</span> Seconds</li>
		</ul>
		<div class="rocket-renew-message">
			<p>
				Your <strong>WP Rocket license is about to expire</strong>: you will soon lose access to product updates and support.
			</p>
			<p>
			Renew with a <strong>$19.80 discount</strong> before it is too late, you will only pay <strong>$49.50</strong>!
			</p>
		</div>
		<div class="rocket-renew-cta-container">
			<a href="https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer">Renew now</a>
		</div>
	</div>',
	],
	'testShouldReturnDataWhenLicenseInfiniteAndNotGrandfathered' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => -1,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'next week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'date_created'      => strtotime( 'last year' ),
			] ) ),
			'pricing'   => $pricing,
		],
		'expected' => '<div class="rocket-renewal-banner">
		<ul class="rocket-promo-countdown" id="rocket-renew-countdown">
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-days">0</span> Days</li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-hours">0</span> Hours</li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-minutes">0</span> Minutes</li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-seconds">0</span> Seconds</li>
		</ul>
		<div class="rocket-renew-message">
			<p>
				Your <strong>WP Rocket license is about to expire</strong>: you will soon lose access to product updates and support.
			</p>
			<p>
			Renew before it is too late, you will only pay <strong>$174.30</strong>!</p>
		</div>
		<div class="rocket-renew-cta-container">
			<a href="https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer">Renew now</a>
		</div>
	</div>',
	],
	'testShouldReturnDataWhenLicenseInfiniteAndGrandfathered' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => -1,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'next week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'date_created'      => strtotime( '2019/08/01' ),
			] ) ),
			'pricing'   => $pricing,
		],
		'expected' => '<div class="rocket-renewal-banner">
		<ul class="rocket-promo-countdown" id="rocket-renew-countdown">
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-days">0</span> Days</li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-hours">0</span> Hours</li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-minutes">0</span> Minutes</li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-seconds">0</span> Seconds</li>
		</ul>
		<div class="rocket-renew-message">
			<p>
				Your <strong>WP Rocket license is about to expire</strong>: you will soon lose access to product updates and support.
			</p>
			<p>
			Renew with a <strong>$49.80 discount</strong> before it is too late, you will only pay <strong>$124.50</strong>!
			</p>
		</div>
		<div class="rocket-renew-cta-container">
			<a href="https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer">Renew now</a>
		</div>
	</div>',
	],
];
