<?php

return [
	'testShouldReturnByocdnDriver' => [
		'config'   => [
			'cdn_type' => 'byocdn',
		],
		'expected' => 'byocdn',
	],
	'testShouldReturnByocdnWhenUnknownType' => [
		'config'   => [
			'cdn_type' => 'something_else',
		],
		'expected' => 'byocdn',
	],
	'testShouldReturnRocketcdnPaidDriver' => [
		'config'   => [
			'cdn_type'                => 'rocketcdn',
			'has_active_subscription' => true,
			'is_paid'                 => true,
		],
		'expected' => 'rocketcdn_paid',
	],
	'testShouldReturnRocketcdnFreeWhenActiveButNotPaid' => [
		'config'   => [
			'cdn_type'                => 'rocketcdn',
			'has_active_subscription' => true,
			'is_paid'                 => false,
		],
		'expected' => 'rocketcdn_free',
	],
	'testShouldReturnRocketcdnWhenNoActiveSubscription' => [
		'config'   => [
			'cdn_type'                          => 'rocketcdn',
			'has_active_subscription'           => false,
			'is_cancelled_outside_grace_period' => true,
		],
		'expected' => 'rocketcdn',
	],

	// TC-4.x: Paid CDN in grace period (cancelled + pending_deletion) → resolved as rocketcdn_paid driver.
	'testShouldReturnRocketcdnPaidDriverInGracePeriod' => [
		'config'   => [
			'cdn_type'           => 'rocketcdn',
			'is_in_grace_period' => true,
			'is_paid'            => true,
		],
		'expected' => 'rocketcdn_paid',
	],

	// Task 8.3 regression lock: once the grace period elapses for a previously-paid
	// subscriber (is_cancelled_outside_grace_period() becomes true), get_driver() must
	// resolve away from a stale "rocketcdn_paid" value on every read — no persisted
	// flag write is needed, live resolution handles it. (The exact non-driver value
	// returned here is a pre-existing, separately-tracked latent issue — out of scope
	// for this story — but it must never be "rocketcdn_paid".)
	'testShouldResolveAwayFromStalePaidStateAfterGracePeriodElapses' => [
		'config'   => [
			'cdn_type'                          => 'rocketcdn',
			'has_active_subscription'           => false,
			'is_cancelled_outside_grace_period' => true,
		],
		'expected' => 'rocketcdn',
	],
];
