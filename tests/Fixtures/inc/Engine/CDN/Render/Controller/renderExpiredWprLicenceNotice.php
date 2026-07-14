<?php

return [
	// TC-3.2: Free CDN + WPR expired → notice is rendered.
	'testRendersNoticeForFreeSubscriptionWithExpiredLicense'   => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'subscription_status' => 'running',
			'plan_type'           => 'free',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => true,
			'license_revoked'     => false,
		],
		'expected' => true,
	],

	// TC-3.6: Free CDN + site banned → notice is rendered.
	'testRendersNoticeForFreeSubscriptionWithRevokedLicense'   => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'subscription_status' => 'running',
			'plan_type'           => 'free',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => false,
			'license_revoked'     => true,
		],
		'expected' => true,
	],

	// TC-3.4: Free CDN + license renewed/valid → notice is hidden.
	'testNoNoticeForFreeSubscriptionWithValidLicense'          => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'subscription_status' => 'running',
			'plan_type'           => 'free',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => false,
			'license_revoked'     => false,
		],
		'expected' => false,
	],

	// TC-3.7: Paid CDN + WPR expired → notice never shown for paid subscriptions.
	'testNoNoticeForPaidSubscriptionWithExpiredLicense'        => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'subscription_status' => 'running',
			'plan_type'           => 'paid',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => true,
			'license_revoked'     => false,
		],
		'expected' => false,
	],

	// TC-3.9: BYOCDN + WPR expired → notice never shown for non-rocketcdn types.
	'testNoNoticeForByocdnWithExpiredLicense'                  => [
		'config'   => [
			'cdn_type'            => 'byocdn',
			'subscription_status' => 'cancelled',
			'plan_type'           => 'free',
			'cdn_url'             => '',
			'license_expired'     => true,
			'license_revoked'     => false,
		],
		'expected' => false,
	],

	// Reseller + banned (BANNED_WEBSITE) → expired notice must NOT render (replaced by the banned notice instead).
	'testNoExpiredNoticeForResellerBannedLicense'              => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'subscription_status' => 'running',
			'plan_type'           => 'free',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => false,
			'license_revoked'     => true,
			'is_reseller'         => true,
			'ban_reason'          => 'BANNED_WEBSITE',
		],
		'expected' => false,
	],

	// Reseller + expired-only (not revoked) → regression: still shows the expired notice.
	'testExpiredNoticeStillShownForResellerExpiredOnly'        => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'subscription_status' => 'running',
			'plan_type'           => 'free',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => true,
			'license_revoked'     => false,
			'is_reseller'         => true,
		],
		'expected' => true,
	],

	// Non-reseller + revoked → regression: still shows the expired notice.
	'testExpiredNoticeStillShownForNonResellerRevoked'         => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'subscription_status' => 'running',
			'plan_type'           => 'free',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => false,
			'license_revoked'     => true,
			'is_reseller'         => false,
			'ban_reason'          => 'BANNED_WEBSITE',
		],
		'expected' => true,
	],

	// Reseller + revoked with unrecognized ban reason → documents accepted-limitation fallback: still shows expired notice.
	'testExpiredNoticeStillShownForResellerRevokedUnrecognizedReason' => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'subscription_status' => 'running',
			'plan_type'           => 'free',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => false,
			'license_revoked'     => true,
			'is_reseller'         => true,
			'ban_reason'          => 'NON_PAYMENT',
		],
		'expected' => true,
	],
];
