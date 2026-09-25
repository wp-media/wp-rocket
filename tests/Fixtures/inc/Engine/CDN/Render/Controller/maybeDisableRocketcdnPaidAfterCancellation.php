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
		'expected' => [
			'persistent' => true,
		],
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
		'expected' => [
			'persistent' => true,
		],
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
		'expected' => [
			'persistent' => true,
		],
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
		'expected' => [
			'persistent' => false,
		],
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
		'expected' => [
			'cdn_state'     => \WP_Rocket\Engine\CDN\Context::CDN_STATE_NOTHING,
			'persistent'    => false,
			// is_forced_off() is still true here regardless of cdn_state (paid + cancelled outside
			// grace period), so cdn_status short-circuits to 'forced_off' before ever consulting
			// the freshly-written cdn_state - this scenario doesn't by itself prove the
			// stale-snapshot fix, see testFreeSubscriptionInactiveResolvesForcedOff for that.
			'tracked_event' => [
				'event'      => 'RocketCDN Mode Changed',
				'properties' => [
					'cdn_mode'   => \WP_Rocket\Engine\CDN\Context::CDN_STATE_NOTHING,
					'cdn_status' => 'forced_off',
					'trigger'    => 'pro_cancellation',
				],
			],
		],
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
		'expected' => [
			'cdn_state'     => \WP_Rocket\Engine\CDN\Context::CDN_STATE_NOTHING,
			'persistent'    => false,
			// Regression lock: is_forced_off() is false here (free plan, valid license), so
			// cdn_status must be derived from the freshly-written 'nothing' cdn_state
			// ('inactive'). Before the stale-snapshot fix this read the pre-write cdn_state
			// (defaults to rocketcdn_paid) instead, wrongly reporting 'active'.
			'tracked_event' => [
				'event'      => 'RocketCDN Mode Changed',
				'properties' => [
					'cdn_mode'   => \WP_Rocket\Engine\CDN\Context::CDN_STATE_NOTHING,
					'cdn_status' => 'inactive',
					'trigger'    => 'pro_cancellation',
				],
			],
		],
	],

	// The user has since switched to BYOCDN: bails out immediately (before touching the
	// persistent tracking flag or cdn_state), so neither is affected by this stale RocketCDN
	// cleanup, regardless of what the underlying subscription state would otherwise resolve to.
	'testByocdnStateNotOverwritten'                        => [
		'config'   => [
			'initial_cdn_state'     => \WP_Rocket\Engine\CDN\Context::BYOCDN_TYPE,
			'subscription_status'   => 'cancelled',
			'plan_type'             => 'paid',
			'license_expired'       => false,
			'license_revoked'       => false,
			'forced_off_persistent' => true,
		],
		'expected' => [
			'persistent' => true,
		],
	],
];
