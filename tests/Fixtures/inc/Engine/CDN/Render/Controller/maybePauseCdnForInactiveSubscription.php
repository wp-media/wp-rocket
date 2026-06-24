<?php

return [
	// TC-3.1: Free CDN + WPR expired → is_forced_paused() = true → returns false.
	'testFreeSubscriptionWithExpiredLicenseForcePaused'                    => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'cdn_option'          => 1,
			'subscription_status' => 'running',
			'plan_type'           => 'free',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => true,
			'license_revoked'     => false,
		],
		'expected' => false,
	],

	// TC-3.6: Free CDN + site banned (revoked) → is_forced_paused() = true → returns false.
	'testFreeSubscriptionWithRevokedLicenseForcePaused'                    => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'cdn_option'          => 1,
			'subscription_status' => 'running',
			'plan_type'           => 'free',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => false,
			'license_revoked'     => true,
		],
		'expected' => false,
	],

	// TC-3.4: Free CDN + valid license → is_forced_paused() = false → cdn_option passes through.
	'testFreeSubscriptionWithValidLicenseNotForcePaused'                   => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'cdn_option'          => 1,
			'subscription_status' => 'running',
			'plan_type'           => 'free',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => false,
			'license_revoked'     => false,
		],
		'expected' => 1,
	],

	// TC-3.7: Paid CDN + WPR expired → paid subscriptions are not force-paused by license expiry.
	'testPaidSubscriptionWithExpiredLicenseNotForcePaused'                 => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'cdn_option'          => 1,
			'subscription_status' => 'running',
			'plan_type'           => 'paid',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => true,
			'license_revoked'     => false,
		],
		'expected' => 1,
	],

	// TC-3.8: Paid CDN manually paused (cdn=0) + WPR expires → manual pause preserved, value stays 0.
	'testPaidSubscriptionManuallyPausedWithExpiredLicensePreservesPause'   => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'cdn_option'          => 0,
			'subscription_status' => 'running',
			'plan_type'           => 'paid',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => true,
			'license_revoked'     => false,
		],
		'expected' => 0,
	],

	// TC-3.9: BYOCDN + WPR expired → context is not rocketcdn, cdn_option passes through unchanged.
	'testByocdnWithExpiredLicenseNotAffected'                              => [
		'config'   => [
			'cdn_type'            => 'byocdn',
			'cdn_option'          => 1,
			'subscription_status' => 'cancelled',
			'plan_type'           => 'free',
			'cdn_url'             => '',
			'license_expired'     => true,
			'license_revoked'     => false,
		],
		'expected' => 1,
	],

	// TC-3.5: Free CDN manually paused before WPR expired → manual pause is 0, still force-pauses to false.
	'testFreeSubscriptionManuallyPausedBeforeExpiry'                       => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'cdn_option'          => 0,
			'subscription_status' => 'running',
			'plan_type'           => 'free',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => true,
			'license_revoked'     => false,
		],
		'expected' => false,
	],
];
