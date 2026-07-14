<?php

return [
	// Reseller + banned + free + active subscription → banned notice rendered.
	'testRendersNoticeForResellerBannedFreeSubscription' => [
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
		'expected' => true,
	],

	// Paid tier reseller banned → no notice (free-tier only feature).
	'testNoNoticeForResellerBannedPaidSubscription'      => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'subscription_status' => 'running',
			'plan_type'           => 'paid',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => false,
			'license_revoked'     => true,
			'is_reseller'         => true,
			'ban_reason'          => 'BANNED_WEBSITE',
		],
		'expected' => false,
	],

	// Non-reseller revoked (even with BANNED_WEBSITE reason) → no banned notice, is_reseller_account() is false.
	'testNoNoticeForNonResellerRevoked'                  => [
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
		'expected' => false,
	],

	// Reseller not revoked → no banned notice.
	'testNoNoticeForResellerNotRevoked'                  => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'subscription_status' => 'running',
			'plan_type'           => 'free',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => false,
			'license_revoked'     => false,
			'is_reseller'         => true,
		],
		'expected' => false,
	],

	// Reseller revoked with unrecognized ban reason → no banned notice (falls back to expired notice instead).
	'testNoNoticeForResellerRevokedUnrecognizedReason'   => [
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
		'expected' => false,
	],

	// Reseller banned but no active subscription → no banned notice.
	'testNoNoticeForResellerBannedNoActiveSubscription'  => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'subscription_status' => 'cancelled',
			'plan_type'           => 'free',
			'cdn_url'             => '',
			'license_expired'     => false,
			'license_revoked'     => true,
			'is_reseller'         => true,
			'ban_reason'          => 'BANNED_WEBSITE',
		],
		'expected' => false,
	],
];
