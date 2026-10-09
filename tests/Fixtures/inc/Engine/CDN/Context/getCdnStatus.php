<?php

return [
	'testShouldReturnOngoingActivationWhenSubscriptionLoading'      => [
		'config'   => [
			'is_subscription_creation_loading' => true,
		],
		'expected' => 'ongoing_activation',
	],
	'testShouldReturnForcedOffWhenPaidInGracePeriod'                => [
		'config'   => [
			'is_paid'            => true,
			'is_in_grace_period' => true,
		],
		'expected' => 'forced_off',
	],
	'testShouldReturnForcedOffWhenPaidCancelledOutsideGracePeriod'  => [
		'config'   => [
			'is_paid'                          => true,
			'is_cancelled_outside_grace_period' => true,
		],
		'expected' => 'forced_off',
	],
	'testShouldReturnForcedOffWhenFreeWithInvalidLicense'           => [
		'config'   => [
			'is_free'            => true,
			'is_license_invalid' => true,
		],
		'expected' => 'forced_off',
	],
	// Context::is_forced_off() doesn't check is_reseller_license_banned() — Controller
	// handles a banned reseller as its own, separate precedence tier ahead of is_forced_off().
	'testShouldReturnInactiveWhenResellerLicenseBannedAlone'        => [
		'config'   => [
			'is_reseller_license_banned' => true,
		],
		'expected' => 'inactive',
	],
	'testShouldReturnActiveWhenModeAppliedAndNotForcedOff'          => [
		'config'   => [
			'cdn_state' => 'rocketcdn_free',
		],
		'expected' => 'active',
	],
	'testShouldReturnInactiveWhenModeNoneAndNotForcedOff'           => [
		'config'   => [
			'cdn_state' => 'nothing',
		],
		'expected' => 'inactive',
	],
	// The override lets a caller that just wrote a new mode compute status against it
	// instead of the stale, per-request Options_Data snapshot (still 'nothing' here).
	'testShouldReturnActiveWhenOverrideModeAppliedDespiteStalePersistedNothing' => [
		'config'   => [
			'cdn_state'          => 'nothing',
			'cdn_state_override' => 'rocketcdn_free',
		],
		'expected' => 'active',
	],
];
