<?php

return [
    'testShouldNotDisplayNoticeWhenUserIsNotRevoked' => [
        'config' => [
            'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'next year' ),
                'licence' => (object) [
                    'is_revoked' => false,
                ],
			] ) ),
            'current_user_can' => true,
            'current_screen' => 'settings_page_wp-crontrol-schedules',
        ],
        'expected' => '',
    ],
    'testShouldNotDisplayNoticeWhenUserDoesNotHaveRightCapability' => [
        'config' => [
            'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'next year' ),
                'licence' => (object) [
                    'is_revoked' => true,
                ],
			] ) ),
            'current_user_can' => false,
            'current_screen' => 'settings_page_wp-crontrol-schedules',
        ],
        'expected' => '',
    ],
    'testShouldNotDisplayNoticeWhenOnSettingsPage' => [
        'config' => [
            'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'next year' ),
                'licence' => (object) [
                    'is_revoked' => true,
                ],
			] ) ),
            'current_user_can' => true,
            'current_screen' => 'settings_page_wprocket',
        ],
        'expected' => '',
    ],
    'testShouldNotDisplayNoticeWhenWhiteLabel' => [
        'config' => [
            'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'last year' ),
                'licence' => (object) [
                    'is_revoked' => true,
                ],
			] ) ),
            'current_user_can' => true,
            'current_screen' => 'settings_page_wp-crontrol-schedules',
            'white_label' => true,
        ],
        'expected' => '',
    ],
    'testShouldDisplayNotice' => [
        'config' => [
            'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'last year' ),
                'licence' => (object) [
                    'is_revoked' => true,
                ],
			] ) ),
            'current_user_can' => true,
            'current_screen' => 'settings_page_wp-crontrol-schedules',
        ],
        'expected' => '<div class="notice notice-error ">
		<p><strong>WP Rocket</strong>: Your license has been revoked and your site is no longer optimized for speed. <a href="https://wp-rocket.me/order/?add-to-cart=191&coupon_code=back2rocket" target="_blank" rel="noopener noreferrer">Get WP Rocket at 20% off</a></p>			</div>',
    ],
    // Reseller account, revoked, ban_reason = BANNED_WEBSITE → notice still shown, same as non-reseller revoked.
    'testShouldDisplayNoticeForResellerBannedAccount' => [
        'config' => [
            'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'last year' ),
				'is_reseller'        => true,
                'licence' => (object) [
                    'is_revoked'                => true,
                    'plugin_updates_ban_reason' => 'BANNED_WEBSITE',
                ],
			] ) ),
            'current_user_can' => true,
            'current_screen' => 'settings_page_wp-crontrol-schedules',
        ],
        'expected' => '<div class="notice notice-error ">
		<p><strong>WP Rocket</strong>: Your license has been revoked and your site is no longer optimized for speed. <a href="https://wp-rocket.me/order/?add-to-cart=191&coupon_code=back2rocket" target="_blank" rel="noopener noreferrer">Get WP Rocket at 20% off</a></p>			</div>',
    ],
    // Reseller account, revoked for a non-banned reason → regression guard: notice still shown, same as non-reseller revoked.
    'testShouldDisplayNoticeForResellerRevokedOtherReason' => [
        'config' => [
            'user' => json_decode( json_encode( [
				'licence_account'    => 1,
				'has_auto_renew'     => false,
				'licence_expiration' => strtotime( 'last year' ),
				'is_reseller'        => true,
                'licence' => (object) [
                    'is_revoked'                => true,
                    'plugin_updates_ban_reason' => '',
                ],
			] ) ),
            'current_user_can' => true,
            'current_screen' => 'settings_page_wp-crontrol-schedules',
        ],
        'expected' => '<div class="notice notice-error ">
		<p><strong>WP Rocket</strong>: Your license has been revoked and your site is no longer optimized for speed. <a href="https://wp-rocket.me/order/?add-to-cart=191&coupon_code=back2rocket" target="_blank" rel="noopener noreferrer">Get WP Rocket at 20% off</a></p>			</div>',
    ],
];