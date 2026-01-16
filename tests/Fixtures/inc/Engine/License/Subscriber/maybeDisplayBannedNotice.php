<?php

return [
    'testShouldNotDisplayNoticeWhenLicenseIsExpired' => [
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
            'current_screen' => 'settings_page_wp-crontrol-schedules',
        ],
        'expected' => '',
    ],
    'testShouldNotDisplayNoticeWhenUserIsNotBanned' => [
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
                    'is_banned' => true,
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
                    'is_banned' => true,
                ],
			] ) ),
            'current_user_can' => true,
            'current_screen' => 'settings_page_wprocket',
        ],
        'expected' => '',
    ],
    'testShouldDisplayNotice' => [
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
        'expected' => '<div class="notice notice-error ">
		<p><strong>WP Rocket</strong>: Your license has been revoked and your site is no longer optimized for speed. <a href="" target="_blank" rel="noopener noreferrer">Get WP Rocket at 20% off</a></p>			</div>',
    ],
];