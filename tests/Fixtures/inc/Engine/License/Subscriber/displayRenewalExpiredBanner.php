<?php

$pricing = json_decode( json_encode( [
	'licenses' => [
		'single'=> [
			'prices'=> [
				'renewal'=> [
					'is_grandfather'=> 24.5,
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
					'not_grandfather'=> 174.3,
					'is_expired'=> 199.2
				]
			],
		],
	],
	'renewals' => [
		'extra_days'=> 90,
		'grandfather_date'=> 1567296000,
		'discount_percent'=> [
			'is_grandfather' => 50,
			'not_grandfather'=> 30,
			'is_expired'     => 20,
		],
	],
] ) );

return [
	'testShouldReturnNullWhenLicenseIsNotExpired' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'next year' ),
			] ) ),
			'pricing'   => $pricing,
			'transient' => false,
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenBannerDismissed' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'last year' ),
			] ) ),
			'pricing'   => $pricing,
			'transient' => true,
		],
		'expected' => null,
	],
	'testShouldReturnDataWhenLicenseExpiredForMoreThan90DaysAndSingleAndNotGrandfathered' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'last year' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'date_created'      => strtotime( 'last year' ),
			] ) ),
			'transient' => false,
			'pricing'   => $pricing,
		],
		'expected' => '<div class="rocket-promo-banner" id="rocket-renewal-banner">
		<div class="rocket-expired-message">
			<h3 class="rocket-expired-title">Your WP Rocket license is expired!</h3>
			<p>
			Your website could be much faster if it could take advantage of  our <strong>new features and enhancements.</strong>
			</p>
			<p>
			Renew your license for 1 year and get an immediate <strong>20% off</strong> on your renewal rate: you will only pay <strong>$39.20</strong>!
			</p>
		</div>
		<div class="rocket-expired-cta-container">
			<a href="https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer">Renew now</a>
		</div>
		<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-renewal"><span class="screen-reader-text">Dismiss this notice.</span></button>
	</div>',
	],
	'testShouldReturnDataWhenLicenseExpiredForLessThan90DaysAndSingleAndNotGrandfathered' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'last week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'date_created'      => strtotime( 'last year' ),
			] ) ),
			'transient' => false,
			'pricing'   => $pricing,
		],
		'expected' => '<div class="rocket-promo-banner" id="rocket-renewal-banner">
		<div class="rocket-expired-message">
			<h3 class="rocket-expired-title">Your WP Rocket license is expired!</h3>
			<p>
			Your website could be much faster if it could take advantage of  our <strong>new features and enhancements.</strong>
			</p>
			<p>
			Renew your license for 1 year and get an immediate <strong>30% off</strong> on your renewal rate: you will only pay <strong>$34.30</strong>!
			</p>
		</div>
		<div class="rocket-expired-cta-container">
			<a href="https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer">Renew now</a>
		</div>
		<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-renewal"><span class="screen-reader-text">Dismiss this notice.</span></button>
	</div>',
	],
	'testShouldReturnDataWhenLicenseExpiredForLessThan90DaysAndSingleAndGrandfathered' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'last week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'date_created'      => strtotime( '2019/08/01' ),
			] ) ),
			'transient' => false,
			'pricing'   => $pricing,
		],
		'expected' => '<div class="rocket-promo-banner" id="rocket-renewal-banner">
		<div class="rocket-expired-message">
			<h3 class="rocket-expired-title">Your WP Rocket license is expired!</h3>
			<p>
			Your website could be much faster if it could take advantage of  our <strong>new features and enhancements.</strong>
			</p>
			<p>
			Renew your license for 1 year and get an immediate <strong>50% off</strong> on your renewal rate: you will only pay <strong>$24.50</strong>!
			</p>
		</div>
		<div class="rocket-expired-cta-container">
			<a href="https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer">Renew now</a>
		</div>
		<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-renewal"><span class="screen-reader-text">Dismiss this notice.</span></button>
	</div>',
	],
	'testShouldReturnDataWhenLicenseExpiredForMoreThan90DaysAndPlusAndNotGrandfathered' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 3,
				'licence_expiration' => strtotime( 'last year' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'date_created'      => strtotime( 'last year' ),
			] ) ),
			'transient' => false,
			'pricing'   => $pricing,
		],
		'expected' => '<div class="rocket-promo-banner" id="rocket-renewal-banner">
		<div class="rocket-expired-message">
			<h3 class="rocket-expired-title">Your WP Rocket license is expired!</h3>
			<p>
			Your website could be much faster if it could take advantage of  our <strong>new features and enhancements.</strong>
			</p>
			<p>
			Renew your license for 1 year and get an immediate <strong>20% off</strong> on your renewal rate: you will only pay <strong>$79.20</strong>!
			</p>
		</div>
		<div class="rocket-expired-cta-container">
			<a href="https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer">Renew now</a>
		</div>
		<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-renewal"><span class="screen-reader-text">Dismiss this notice.</span></button>
	</div>',
	],
	'testShouldReturnDataWhenLicenseExpiredForLessThan90DaysAndPlusAndNotGrandfathered' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 3,
				'licence_expiration' => strtotime( 'last week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'date_created'      => strtotime( 'last year' ),
			] ) ),
			'transient' => false,
			'pricing'   => $pricing,
		],
		'expected' => '<div class="rocket-promo-banner" id="rocket-renewal-banner">
		<div class="rocket-expired-message">
			<h3 class="rocket-expired-title">Your WP Rocket license is expired!</h3>
			<p>
			Your website could be much faster if it could take advantage of  our <strong>new features and enhancements.</strong>
			</p>
			<p>
			Renew your license for 1 year and get an immediate <strong>30% off</strong> on your renewal rate: you will only pay <strong>$69.30</strong>!
			</p>
		</div>
		<div class="rocket-expired-cta-container">
			<a href="https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer">Renew now</a>
		</div>
		<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-renewal"><span class="screen-reader-text">Dismiss this notice.</span></button>
	</div>',
	],
	'testShouldReturnDataWhenLicenseExpiredForLessThan90DaysAndPlusAndGrandfathered' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 3,
				'licence_expiration' => strtotime( 'last week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'date_created'      => strtotime( '2019/08/01' ),
			] ) ),
			'transient' => false,
			'pricing'   => $pricing,
		],
		'expected' => '<div class="rocket-promo-banner" id="rocket-renewal-banner">
		<div class="rocket-expired-message">
			<h3 class="rocket-expired-title">Your WP Rocket license is expired!</h3>
			<p>
			Your website could be much faster if it could take advantage of  our <strong>new features and enhancements.</strong>
			</p>
			<p>
			Renew your license for 1 year and get an immediate <strong>50% off</strong> on your renewal rate: you will only pay <strong>$49.50</strong>!
			</p>
		</div>
		<div class="rocket-expired-cta-container">
			<a href="https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer">Renew now</a>
		</div>
		<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-renewal"><span class="screen-reader-text">Dismiss this notice.</span></button>
	</div>',
	],
	'testShouldReturnDataWhenLicenseExpiredForMoreThan90DaysAndInfiniteAndNotGrandfathered' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => -1,
				'licence_expiration' => strtotime( 'last year' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'date_created'      => strtotime( 'last year' ),
			] ) ),
			'transient' => false,
			'pricing'   => $pricing,
		],
		'expected' => '<div class="rocket-promo-banner" id="rocket-renewal-banner">
		<div class="rocket-expired-message">
			<h3 class="rocket-expired-title">Your WP Rocket license is expired!</h3>
			<p>
			Your website could be much faster if it could take advantage of  our <strong>new features and enhancements.</strong>
			</p>
			<p>
			Renew your license for 1 year and get an immediate <strong>20% off</strong> on your renewal rate: you will only pay <strong>$199.20</strong>!
			</p>
		</div>
		<div class="rocket-expired-cta-container">
			<a href="https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer">Renew now</a>
		</div>
		<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-renewal"><span class="screen-reader-text">Dismiss this notice.</span></button>
	</div>',
	],
	'testShouldReturnDataWhenLicenseExpiredForLessThan90DaysAndInfiniteAndNotGrandfathered' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => -1,
				'licence_expiration' => strtotime( 'last week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'date_created'      => strtotime( 'last year' ),
			] ) ),
			'transient' => false,
			'pricing'   => $pricing,
		],
		'expected' => '<div class="rocket-promo-banner" id="rocket-renewal-banner">
		<div class="rocket-expired-message">
			<h3 class="rocket-expired-title">Your WP Rocket license is expired!</h3>
			<p>
			Your website could be much faster if it could take advantage of  our <strong>new features and enhancements.</strong>
			</p>
			<p>
			Renew your license for 1 year and get an immediate <strong>30% off</strong> on your renewal rate: you will only pay <strong>$174.30</strong>!
			</p>
		</div>
		<div class="rocket-expired-cta-container">
			<a href="https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer">Renew now</a>
		</div>
		<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-renewal"><span class="screen-reader-text">Dismiss this notice.</span></button>
	</div>',
	],
	'testShouldReturnDataWhenLicenseExpiredForLessThan90DaysAndInfiniteAndGrandfathered' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => -1,
				'licence_expiration' => strtotime( 'last week' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'date_created'      => strtotime( '2019/08/01' ),
			] ) ),
			'transient' => false,
			'pricing'   => $pricing,
		],
		'expected' => '<div class="rocket-promo-banner" id="rocket-renewal-banner">
		<div class="rocket-expired-message">
			<h3 class="rocket-expired-title">Your WP Rocket license is expired!</h3>
			<p>
			Your website could be much faster if it could take advantage of  our <strong>new features and enhancements.</strong>
			</p>
			<p>
			Renew your license for 1 year and get an immediate <strong>50% off</strong> on your renewal rate: you will only pay <strong>$124.50</strong>!
			</p>
		</div>
		<div class="rocket-expired-cta-container">
			<a href="https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer">Renew now</a>
		</div>
		<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-renewal"><span class="screen-reader-text">Dismiss this notice.</span></button>
	</div>',
	],
];
