<?php

return [
	'testShouldSetByocdnWhenCdnTypeChangesToByocdn'                     => [
		'config'   => [
			'initial' => [
				'cdn'       => 0,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'nothing',
			],
			'write'   => [
				'cdn'      => 1,
				'cdn_type' => 'byocdn',
			],
		],
		'expected' => [
			'cdn'       => 1,
			'cdn_type'  => 'byocdn',
			'cdn_state' => 'byocdn',
		],
	],
	'testShouldSetNothingWhenCdnIsDisabled'                             => [
		'config'   => [
			'initial'      => [
				'cdn'       => 1,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_free',
			],
			'subscription' => [
				'subscription_status' => 'running',
				'plan_type'           => 'free',
			],
			'write'        => [
				'cdn' => 0,
			],
		],
		'expected' => [
			'cdn'       => 0,
			'cdn_type'  => 'rocketcdn',
			'cdn_state' => 'nothing',
		],
	],
	'testShouldSetRocketcdnFreeWhenSubscriptionActiveAndNotPaid'        => [
		'config'   => [
			'initial'      => [
				'cdn'       => 0,
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'nothing',
			],
			'subscription' => [
				'subscription_status' => 'running',
				'plan_type'           => 'free',
			],
			'write'        => [
				'cdn'      => 1,
				'cdn_type' => 'rocketcdn',
			],
		],
		'expected' => [
			'cdn'       => 1,
			'cdn_type'  => 'rocketcdn',
			'cdn_state' => 'rocketcdn_free',
		],
	],
	'testShouldSetRocketcdnPaidWhenSubscriptionActiveAndPaid'           => [
		'config'   => [
			'initial'      => [
				'cdn'       => 0,
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'nothing',
			],
			'subscription' => [
				'subscription_status' => 'running',
				'plan_type'           => 'paid',
			],
			'write'        => [
				'cdn'      => 1,
				'cdn_type' => 'rocketcdn',
			],
		],
		'expected' => [
			'cdn'       => 1,
			'cdn_type'  => 'rocketcdn',
			'cdn_state' => 'rocketcdn_paid',
		],
	],
	'testShouldSetNothingWhenSubscriptionCancelledOutsideGracePeriod'   => [
		'config'   => [
			'initial'      => [
				'cdn'       => 0,
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'nothing',
			],
			'subscription' => [
				'subscription_status' => 'cancelled',
				'website_status'      => 'active',
			],
			'write'        => [
				'cdn'      => 1,
				'cdn_type' => 'rocketcdn',
			],
		],
		'expected' => [
			'cdn'       => 1,
			'cdn_type'  => 'rocketcdn',
			'cdn_state' => 'nothing',
		],
	],
	'testShouldSetRocketcdnFreeWhenSubscriptionCancelledButInGracePeriod' => [
		'config'   => [
			'initial'      => [
				'cdn'       => 0,
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'nothing',
			],
			'subscription' => [
				'subscription_status' => 'cancelled',
				'website_status'      => 'pending_deletion',
			],
			'write'        => [
				'cdn'      => 1,
				'cdn_type' => 'rocketcdn',
			],
		],
		'expected' => [
			'cdn'       => 1,
			'cdn_type'  => 'rocketcdn',
			'cdn_state' => 'rocketcdn_free',
		],
	],
	'testShouldSetNothingWhenByocdnCdnIsUnchecked'                      => [
		'config'   => [
			'initial' => [
				'cdn'       => 1,
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'byocdn',
			],
			'write'   => [
				'cdn' => 0,
			],
		],
		'expected' => [
			'cdn'       => 0,
			'cdn_type'  => 'byocdn',
			'cdn_state' => 'nothing',
		],
	],
	'testShouldLeaveCdnStateUntouchedWhenOnlyUnrelatedFieldsChange'     => [
		'config'   => [
			'initial'      => [
				'cdn'       => 1,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_free',
			],
			'subscription' => [
				'subscription_status' => 'running',
				'plan_type'           => 'free',
			],
			'write'        => [
				'cdn_cnames' => [ 'https://example.com' ],
			],
		],
		'expected' => [
			'cdn'        => 1,
			'cdn_type'   => 'rocketcdn',
			'cdn_state'  => 'rocketcdn_free',
			'cdn_cnames' => [ 'https://example.com' ],
		],
	],
];
