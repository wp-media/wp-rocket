<?php

return [
    'testShouldNotDisplayBannerWhenLicenseIsExpired' => [
        'config' => [
            'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'last year' ),
                'licence' => (object) [
                    'is_banned' => true,
                ],
			] ) ),
            'current_user_can' => true,
            'current_screen' => 'settings_page_wprocket',
        ],
        'expected' => '',
    ],
    'testShouldNotDisplayBannerWhenUserIsNotBanned' => [
        'config' => [
            'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'next year' ),
                'licence' => (object) [
                    'is_banned' => false,
                ],
			] ) ),
            'current_user_can' => true,
            'current_screen' => 'settings_page_wprocket',
        ],
        'expected' => '',
    ],
    'testShouldNotDisplayBannerWhenUserDoesNotHaveRightCapability' => [
        'config' => [
            'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'next year' ),
                'licence' => (object) [
                    'is_banned' => true,
                ],
			] ) ),
            'current_user_can' => false,
            'current_screen' => 'settings_page_wprocket',
        ],
        'expected' => '',
    ],
    'testShouldNotDisplayBannerWhenNotOnSettingsPage' => [
        'config' => [
            'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'next year' ),
                'licence' => (object) [
                    'is_banned' => true,
                ],
			] ) ),
            'current_user_can' => true,
            'current_screen' => 'settings_page_wp-crontrol-schedules',
        ],
        'expected' => '',
    ],
    'testShouldDisplayBanner' => [
        'config' => [
            'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'next year' ),
                'licence' => (object) [
                    'is_banned' => true,
                ],
			] ) ),
            'current_user_can' => true,
            'current_screen' => 'settings_page_wprocket',
        ],
        'expected' => '<section class="rocket-renewal-expired-banner banned-website-banner" id="rocket-renewal-banner">
	<div class="banner-copy">
		<h3 class="rocket-expired-title">Your WP Rocket license has been revoked!</h3>
		<div class="rocket-renewal-expired-banner-container">
			<div class="rocket-expired-message">
								<p>As your license is no longer active, you lost access to WP Rocket&#039;s powerful features to <strong>boost speed</strong> and deliver a <strong>top-notch user experience</strong>.</p>
							</div>
		</div>
	</div>
	<div class="rocket-expired-cta-container">
		<a href="https://wp-rocket.me/order/?add-to-cart=191&#038;coupon_code=back2rocket" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer">GET WP ROCKET AT 20% OFF</a>
	</div>
</section>',
    ],
];