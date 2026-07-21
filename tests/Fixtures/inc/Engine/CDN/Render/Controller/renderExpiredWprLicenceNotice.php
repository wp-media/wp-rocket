<?php

return [
	// TC-3.2: Free CDN + WPR expired → notice is rendered.
	'testRendersNoticeForFreeSubscriptionWithExpiredLicense' => [
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

	// Issue #8643: Free CDN + WPR expired + subscription cancelled/deleted at RocketCDN
	// (e.g. cron deleted the free subscription during the grace period, or the website
	// was fully deleted at RocketCDN) → notice must still be rendered.
	'testRendersNoticeForFreeSubscriptionCancelledWithExpiredLicense' => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'subscription_status' => 'cancelled',
			'plan_type'           => 'free',
			'cdn_url'             => '',
			'license_expired'     => true,
			'license_revoked'     => false,
		],
		'expected' => true,
	],

	// TC-3.6: Free CDN + site banned → notice is rendered.
	'testRendersNoticeForFreeSubscriptionWithRevokedLicense' => [
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
	'testNoNoticeForFreeSubscriptionWithValidLicense'   => [
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
	'testNoNoticeForPaidSubscriptionWithExpiredLicense' => [
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
	'testNoNoticeForByocdnWithExpiredLicense'           => [
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

	// Reseller + Free CDN + WPR expired → notice is rendered but Renew Licence button is hidden.
	'testHidesRenewButtonForResellerWithExpiredLicense' => [
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

	// Reseller + Free CDN + site banned → notice is rendered but Renew Licence button is hidden.
	'testHidesRenewButtonForResellerWithRevokedLicense' => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'subscription_status' => 'running',
			'plan_type'           => 'free',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => false,
			'license_revoked'     => true,
			'is_reseller'         => true,
		],
		'expected' => true,
	],
];
