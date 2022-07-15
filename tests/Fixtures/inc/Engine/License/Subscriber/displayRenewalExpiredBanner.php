<?php

return [
	'testShouldReturnNullWhenLicenseIsNotExpired' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'next year' ),
			] ) ),
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
			'transient' => true,
		],
		'expected' => null,
	],
	'testShouldReturnDataWhenLicenseExpired' => [
		'config'   => [
			'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'licence_expiration' => strtotime( 'last year' ),
				'renewal_url'        => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
				'date_created'      => strtotime( 'last year' ),
			] ) ),
			'transient' => false,
		],
		'expected' => '<section class="rocket-renewal-expired-banner" id="rocket-renewal-banner">
		<h3 class="rocket-expired-title">Your WP Rocket license is expired!</h3>
		<div class="rocket-renewal-expired-banner-container">
			<div class="rocket-expired-message">

				<p>
				<strong>Your website could be much faster</strong> if it could take advantage of our new features and enhancements. 🚀
				</p>
				<p>
				Renew your license to have access to the <strong>latest version of WP Rocket</strong> and to the wonderful <strong>assistance of our Support Team</strong>.
				</p>
			</div>
			<div class="rocket-expired-cta-container">
				<a href="https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer">Renew now</a>
			</div>
		</div>
		<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-renewal"><span class="screen-reader-text">Dismiss this notice</span></button>
	</section>',
	],
];
