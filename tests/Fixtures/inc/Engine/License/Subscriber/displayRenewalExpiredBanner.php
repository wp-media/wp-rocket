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
		'grandmother_date'=> 1640995200,
		'discount_percent'=> [
			'is_grandfather' => 20,
			'not_grandfather'=> 0,
			'is_expired'     => 0,
		],
	],
] ) );

return [
	'testShouldReturnNullWhenLicenseIsNotExpired' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'next year' ),
				'is_auto_renew' => false,
			] ) ),
			'pricing' => $pricing,
			'transient' => false,
		],
		'expected' => '',
	],
	'testShouldReturnNullWhenBannerDismissed' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'last year' ),
				'is_auto_renew' => false,
			] ) ),
			'pricing' => $pricing,
			'transient' => true,
		],
		'expected' => '',
	],
	'testShouldDisplayBannerWhenLicenseExpired' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'now - 15 days' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'date_created'      => strtotime( '2022-01-05' ),
				'is_auto_renew' => false,
			] ) ),
			'pricing' => $pricing,
			'transient' => false,
		],
		'expected' => '<section class="rocket-renewal-expired-banner" id="rocket-renewal-banner">
		<h3 class="rocket-expired-title">Your WP Rocket license is expired!</h3>
		<div class="rocket-renewal-expired-banner-container">
			<div class="rocket-expired-message">

				<p>
				Your website could be much faster if it could take advantage of our <strong>new features and enhancements</strong>. 🚀
				</p>
				<p>Renew your license for 1 year now at<strong>$34.30</strong>.</p>
			</div>
			<div class="rocket-expired-cta-container">
				<a href="https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer">Renew now</a>
			</div>
		</div>
		<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-renewal"><span class="screen-reader-text">Dismiss this notice</span></button>
	</section>',
	],
];
