<?php
return [
	// -------------------------------------------------------------------------
	// < 3.22 path (existing cases, updated to include cdn_state)
	// -------------------------------------------------------------------------

	'shouldSetByocdnWhenLegacyCdnIsEnabled'                            => [
		'config'   => [
			'new_version'             => '3.22.0',
			'old_version'             => '3.21.1',
			'current_options'         => [
				'cdn' => 1,
			],
			'has_active_subscription' => false,
			'cdn_cnames'              => [
				'https://cdnexample.org/',
			],
			'cdn_state_from_bridge'   => 'byocdn',
		],
		'expected' => [
			'options' => [
				'cdn'       => 1,
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'byocdn',
			],
		],
	],

	'shouldSetRocketcdnWhenCdnIsNotEnabled'                            => [
		'config'   => [
			'new_version'             => '3.22.0',
			'old_version'             => '3.21.1',
			'current_options'         => [],
			'has_active_subscription' => false,
			'cdn_state_from_bridge'   => 'rocketcdn_free',
		],
		'expected' => [
			'options' => [
				'cdn'       => 1,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_free',
			],
		],
	],

	// cdn disabled but CNAME present: ports to byocdn inactive (cdn_state='nothing').
	// Previous behaviour wrongly forced cdn=1 and defaulted to rocketcdn.
	'shouldSetByocdnNothingWhenCdnDisabledButCnameExists'              => [
		'config'   => [
			'new_version'             => '3.22.0',
			'old_version'             => '3.21.1',
			'current_options'         => [ 'cdn' => 0 ],
			'has_active_subscription' => false,
			'cdn_cnames'              => [ 'https://cdnexample.org/' ],
			'cdn_state_from_bridge'   => 'nothing',
		],
		'expected' => [
			'options' => [
				'cdn'       => 0,
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'nothing',
			],
		],
	],

	'shouldSetRocketcdnWhenHavingRocketcdnSubscription'                => [
		'config'   => [
			'new_version'             => '3.22.0',
			'old_version'             => '3.21.1',
			'current_options'         => [],
			'has_active_subscription' => true,
			'cdn_state_from_bridge'   => 'nothing',
		],
		'expected' => [
			'options' => [
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'nothing',
			],
		],
	],

	// < 3.22 — active Pro subscription + cdn=1: should migrate to rocketcdn_paid.
	'shouldSetRocketcdnPaidWhenActiveProSubscriptionLessThan322'       => [
		'config'   => [
			'new_version'             => '3.22.0',
			'old_version'             => '3.21.1',
			'current_options'         => [ 'cdn' => 1 ],
			'has_active_subscription' => true,
			'cdn_state_from_bridge'   => 'rocketcdn_paid',
		],
		'expected' => [
			'options' => [
				'cdn'       => 1,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_paid',
			],
		],
	],

	// -------------------------------------------------------------------------
	// >= 3.22 path (cdn_type already set, cdn_state is new)
	// -------------------------------------------------------------------------

	'shouldSetNothingWhenFreePausedGreaterThanOrEqual322'              => [
		'config'   => [
			'new_version'           => '3.26.0',
			'old_version'           => '3.22.0',
			'current_options'       => [
				'cdn'      => 0,
				'cdn_type' => 'rocketcdn',
			],
			'cdn_state_from_bridge' => 'nothing',
		],
		'expected' => [
			'options' => [
				'cdn'       => 0,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'nothing',
			],
		],
	],

	'shouldSetRocketcdnFreeWhenFreeActiveGreaterThanOrEqual322'        => [
		'config'   => [
			'new_version'           => '3.26.0',
			'old_version'           => '3.22.0',
			'current_options'       => [
				'cdn'      => 1,
				'cdn_type' => 'rocketcdn',
			],
			'cdn_state_from_bridge' => 'rocketcdn_free',
		],
		'expected' => [
			'options' => [
				'cdn'       => 1,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_free',
			],
		],
	],

	'shouldSetNothingWhenProPausedGreaterThanOrEqual322'               => [
		'config'   => [
			'new_version'           => '3.26.0',
			'old_version'           => '3.22.0',
			'current_options'       => [
				'cdn'      => 0,
				'cdn_type' => 'rocketcdn',
			],
			'cdn_state_from_bridge' => 'nothing',
		],
		'expected' => [
			'options' => [
				'cdn'       => 0,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'nothing',
			],
		],
	],

	'shouldSetRocketcdnPaidWhenProActiveGreaterThanOrEqual322'         => [
		'config'   => [
			'new_version'           => '3.26.0',
			'old_version'           => '3.22.0',
			'current_options'       => [
				'cdn'      => 1,
				'cdn_type' => 'rocketcdn',
			],
			'cdn_state_from_bridge' => 'rocketcdn_paid',
		],
		'expected' => [
			'options' => [
				'cdn'       => 1,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_paid',
			],
		],
	],

	'shouldSetNothingWhenByocdnPausedGreaterThanOrEqual322'            => [
		'config'   => [
			'new_version'           => '3.26.0',
			'old_version'           => '3.22.0',
			'current_options'       => [
				'cdn'      => 0,
				'cdn_type' => 'byocdn',
			],
			'cdn_state_from_bridge' => 'nothing',
		],
		'expected' => [
			'options' => [
				'cdn'       => 0,
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'nothing',
			],
		],
	],

	'shouldSetByocdnWhenByocdnActiveGreaterThanOrEqual322'             => [
		'config'   => [
			'new_version'           => '3.26.0',
			'old_version'           => '3.22.0',
			'current_options'       => [
				'cdn'      => 1,
				'cdn_type' => 'byocdn',
			],
			'cdn_state_from_bridge' => 'byocdn',
		],
		'expected' => [
			'options' => [
				'cdn'       => 1,
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'byocdn',
			],
		],
	],

	'shouldSetNothingWhenCancelledOutsideGracePeriodGreaterThanOrEqual322' => [
		'config'   => [
			'new_version'           => '3.26.0',
			'old_version'           => '3.22.0',
			'current_options'       => [
				'cdn'      => 1,
				'cdn_type' => 'rocketcdn',
			],
			'cdn_state_from_bridge' => 'nothing',
		],
		'expected' => [
			'options' => [
				'cdn'       => 1,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'nothing',
			],
		],
	],

	'shouldSetRocketcdnFreeWhenInGracePeriodGreaterThanOrEqual322'     => [
		'config'   => [
			'new_version'           => '3.26.0',
			'old_version'           => '3.22.0',
			'current_options'       => [
				'cdn'      => 1,
				'cdn_type' => 'rocketcdn',
			],
			'cdn_state_from_bridge' => 'rocketcdn_free',
		],
		'expected' => [
			'options' => [
				'cdn'       => 1,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_free',
			],
		],
	],
];
