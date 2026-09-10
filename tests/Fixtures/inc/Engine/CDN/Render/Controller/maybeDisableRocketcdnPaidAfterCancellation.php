<?php

return [
	// Active subscription -> bails on the very first check -> no write.
	'testActiveSubscriptionNotAffected'                    => [
		'config'   => [
			'subscription_status'   => 'running',
			'plan_type'             => 'paid',
			'license_expired'       => false,
			'license_revoked'       => false,
			'forced_off_persistent' => true,
		],
		'expected' => false,
	],

	// Paid + still within the cancellation grace period -> bails -> no write.
	'testPaidGracePeriodNotAffected'                       => [
		'config'   => [
			'subscription_status'   => 'cancelled',
			'plan_type'             => 'paid',
			'website_status'        => 'pending_deletion',
			'license_expired'       => false,
			'license_revoked'       => false,
			'forced_off_persistent' => true,
		],
		'expected' => false,
	],

	// WP Rocket licence invalid -> bails regardless of subscription state -> no write.
	'testInvalidLicenseNotAffected'                        => [
		'config'   => [
			'subscription_status'   => 'cancelled',
			'plan_type'             => 'paid',
			'license_expired'       => true,
			'license_revoked'       => false,
			'forced_off_persistent' => true,
		],
		'expected' => false,
	],

	// Grace period elapsed, licence valid, but forced-off tracking was never persistent (nothing to resolve) -> no write.
	'testPersistentFlagNotSetNotAffected'                  => [
		'config'   => [
			'subscription_status'   => 'cancelled',
			'plan_type'             => 'paid',
			'license_expired'       => false,
			'license_revoked'       => false,
			'forced_off_persistent' => false,
		],
		'expected' => false,
	],

	// Grace period elapsed (cancelled, not pending_deletion), licence valid, forced-off tracking was persistent
	// -> writes cdn_state = nothing and resets the persistent tracking flag.
	'testPaidCancelledOutsideGracePeriodResolvesForcedOff' => [
		'config'   => [
			'subscription_status'   => 'cancelled',
			'plan_type'             => 'paid',
			'license_expired'       => false,
			'license_revoked'       => false,
			'forced_off_persistent' => true,
		],
		'expected' => true,
	],

	// Free tier, no active subscription, licence valid, forced-off tracking was persistent -> writes cdn_state = nothing.
	'testFreeSubscriptionInactiveResolvesForcedOff'        => [
		'config'   => [
			'subscription_status'   => 'cancelled',
			'plan_type'             => 'free',
			'license_expired'       => false,
			'license_revoked'       => false,
			'forced_off_persistent' => true,
		],
		'expected' => true,
	],
];
