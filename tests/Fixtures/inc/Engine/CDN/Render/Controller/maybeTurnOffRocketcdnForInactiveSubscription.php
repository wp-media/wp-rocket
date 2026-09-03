<?php

return [
	// BYOCDN: applied_cdn_state resolves to 'byocdn', never 'rocketcdn' — bails before is_forced_off() runs.
	'testByocdnNotAffected'                                     => [
		'config'   => [
			'cdn_type'            => 'byocdn',
			'cdn_option'          => 1,
			'subscription_status' => 'running',
			'plan_type'           => 'paid',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => false,
			'license_revoked'     => false,
		],
		'expected' => false,
	],

	// CDN already manually off: applied_cdn_state resolves to 'nothing' regardless of subscription — bails.
	'testCdnAlreadyOffNotAffected'                               => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'cdn_option'          => 0,
			'subscription_status' => 'running',
			'plan_type'           => 'paid',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => false,
			'license_revoked'     => false,
		],
		'expected' => false,
	],

	// Free CDN + WPR licence expired -> is_forced_off() true (is_free && is_license_invalid) -> writes cdn_state = nothing.
	'testFreeSubscriptionWithExpiredLicenseForcedOff'            => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'cdn_option'          => 1,
			'subscription_status' => 'running',
			'plan_type'           => 'free',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => true,
			'license_revoked'     => false,
		],
		'expected' => true,
	],

	// Free CDN + valid licence -> not forced off -> no write.
	'testFreeSubscriptionWithValidLicenseNotForcedOff'           => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'cdn_option'          => 1,
			'subscription_status' => 'running',
			'plan_type'           => 'free',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => false,
			'license_revoked'     => false,
		],
		'expected' => false,
	],

	// Paid CDN cancelled but still within the grace period (pending_deletion) -> legacy_to_state still resolves
	// 'rocketcdn_paid' live (is_cancelled_outside_grace_period() is false during grace), so the driver check
	// passes through to is_forced_off(), which is true (is_paid && is_in_grace_period) -> writes cdn_state = nothing.
	'testPaidGracePeriodForcedOff'                                => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'cdn_option'          => 1,
			'subscription_status' => 'cancelled',
			'plan_type'           => 'paid',
			'website_status'      => 'pending_deletion',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => false,
			'license_revoked'     => false,
		],
		'expected' => true,
	],

	// Paid CDN cancelled and grace period elapsed (no longer pending_deletion): CdnStateBridge's live resolver
	// already returns 'nothing' for this case (is_cancelled_outside_grace_period() short-circuits legacy_to_state
	// before is_paid() is even checked), so applied_cdn_state is never 'rocketcdn' and the method bails on its
	// very first check -- it never reaches is_forced_off() or writes anything itself for this transition.
	'testPaidCancelledOutsideGracePeriodBailsOnDriverCheck'       => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'cdn_option'          => 1,
			'subscription_status' => 'cancelled',
			'plan_type'           => 'paid',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => false,
			'license_revoked'     => false,
		],
		'expected' => false,
	],

	// Active, healthy paid subscription -> not forced off -> no write.
	'testActivePaidSubscriptionNotForcedOff'                      => [
		'config'   => [
			'cdn_type'            => 'rocketcdn',
			'cdn_option'          => 1,
			'subscription_status' => 'running',
			'plan_type'           => 'paid',
			'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			'license_expired'     => false,
			'license_revoked'     => false,
		],
		'expected' => false,
	],
];
