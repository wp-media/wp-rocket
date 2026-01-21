<?php

return [
    'testShouldNotAddBubbleWhenLicenseIsExpired' => [
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
        ],
        'expected' => 'WP Rocket',
    ],
    'testShouldNotAddBubbleWhenUserIsNotBanned' => [
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
        ],
        'expected' => 'WP Rocket',
    ],
    'testShouldNotAddBubbleWhenUserDoesNotHaveRightCapability' => [
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
        ],
        'expected' => 'WP Rocket',
    ],
    'testShouldAddBubble' => [
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
        ],
        'expected' => 'WP Rocket <span class="rocket-banned-bubble"></span>',
    ],
];