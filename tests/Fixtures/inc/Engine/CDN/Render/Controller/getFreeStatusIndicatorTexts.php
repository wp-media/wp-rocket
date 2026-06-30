<?php

$base_texts = [
	'paused_status_text' => 'RocketCDN is paused',
	'active_status_text' => 'RocketCDN is active',
	'paused_details'     => 'RocketCDN is currently paused. Click Resume CDN to re-enable content delivery.',
	'status_text'        => '',
	'details'            => 'Start with your homepage...',
	'class'              => '',
];

return [
	// TC-3.2: Free CDN + WPR expired → adds wpr-cdn-status--expired CSS class to indicator.
	'testAddsExpiredClassForFreeSubscriptionWithExpiredLicense'   => [
		'config'   => [
			'subscription_status' => 'running',
			'plan_type'           => 'free',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'pages_count'         => 1,
			'is_loading'          => false,
			'free'                => true,
			'license_expired'     => true,
			'license_revoked'     => false,
		],
		'expected' => [
			'class_contains' => 'wpr-cdn-status--expired',
		],
	],

	// TC-3.6: Free CDN + site banned → same expired class added.
	'testAddsExpiredClassForFreeSubscriptionWithRevokedLicense'   => [
		'config'   => [
			'subscription_status' => 'running',
			'plan_type'           => 'free',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'pages_count'         => 1,
			'is_loading'          => false,
			'free'                => true,
			'license_expired'     => false,
			'license_revoked'     => true,
		],
		'expected' => [
			'class_contains' => 'wpr-cdn-status--expired',
		],
	],

	// TC-3.4: Free CDN + valid license → no expired class.
	'testNoExpiredClassForFreeSubscriptionWithValidLicense'       => [
		'config'   => [
			'subscription_status' => 'running',
			'plan_type'           => 'free',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'pages_count'         => 1,
			'is_loading'          => false,
			'free'                => true,
			'license_expired'     => false,
			'license_revoked'     => false,
		],
		'expected' => [
			'class_contains' => '',
		],
	],

	// TC-3.7: Paid subscription → free=false, method returns texts unchanged (paid tier callback handles it).
	'testReturnedUnchangedForPaidSubscription'                    => [
		'config'   => [
			'subscription_status' => 'running',
			'plan_type'           => 'paid',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'pages_count'         => 0,
			'is_loading'          => false,
			'free'                => false,
			'license_expired'     => true,
			'license_revoked'     => false,
		],
		'expected' => [
			'class_contains' => '',
		],
	],
];
