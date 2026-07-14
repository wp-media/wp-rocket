<?php

return [
    'testShouldNotAddBubbleWhenUserIsNotRevoked' => [
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
                    'is_revoked' => true,
                ],
			] ) ),
            'current_user_can' => false,
        ],
        'expected' => 'WP Rocket',
    ],
    'testShouldNotAddBubbleWhenWhiteLabel' => [
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
            'white_label' => true,
        ],
        'expected' => 'WP Rocket',
    ],
    'testShouldAddBubble' => [
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
        ],
        'expected' => 'WP Rocket <span class="rocket-revoked-bubble"></span>',
    ],
    // Reseller account, revoked, ban_reason = BANNED_WEBSITE → bubble suppressed (Problem B fix, narrowed).
    'testShouldNotAddBubbleForResellerBannedAccount' => [
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
        ],
        'expected' => 'WP Rocket',
    ],
    // Reseller account, revoked for a non-banned reason → regression guard: bubble still added, same as non-reseller revoked.
    'testShouldAddBubbleForResellerRevokedOtherReason' => [
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
        ],
        'expected' => 'WP Rocket <span class="rocket-revoked-bubble"></span>',
    ],
];