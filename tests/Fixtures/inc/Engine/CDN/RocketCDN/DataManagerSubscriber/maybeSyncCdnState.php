<?php

return [
	'testShouldDowngradeProToFreeWhenPlanTypeIsFree'     => [
		'config'   => [
			'initial_cdn_state' => 'rocketcdn_paid',
			'transient_value'   => [
				'plan_type'   => 'free',
				'status_code' => 200,
			],
		],
		'expected' => [
			'cdn_state' => 'rocketcdn_free',
		],
	],
	'testShouldUpgradeFreeToProWhenPlanTypeIsPaid'       => [
		'config'   => [
			'initial_cdn_state' => 'rocketcdn_free',
			'transient_value'   => [
				'plan_type'   => 'paid',
				'status_code' => 200,
			],
		],
		'expected' => [
			'cdn_state' => 'rocketcdn_paid',
		],
	],
	'testShouldLeaveProUnchangedWhenPlanTypeStillPaid'   => [
		'config'   => [
			'initial_cdn_state' => 'rocketcdn_paid',
			'transient_value'   => [
				'plan_type'   => 'paid',
				'status_code' => 200,
			],
		],
		'expected' => [
			'cdn_state' => 'rocketcdn_paid',
		],
	],
	'testShouldNotTouchByocdnState'                      => [
		'config'   => [
			'initial_cdn_state' => 'byocdn',
			'transient_value'   => [
				'plan_type'   => 'free',
				'status_code' => 200,
			],
		],
		'expected' => [
			'cdn_state' => 'byocdn',
		],
	],
	'testShouldActivateFromNothingWhenPlanTypeIsPaid'    => [
		'config'   => [
			'initial_cdn_state' => 'nothing',
			'transient_value'   => [
				'plan_type'   => 'paid',
				'status_code' => 200,
			],
		],
		'expected' => [
			'cdn_state' => 'rocketcdn_paid',
		],
	],
	'testShouldActivateFromNothingWhenPlanTypeIsFree'    => [
		'config'   => [
			'initial_cdn_state' => 'nothing',
			'transient_value'   => [
				'plan_type'   => 'free',
				'status_code' => 200,
			],
		],
		'expected' => [
			'cdn_state' => 'rocketcdn_free',
		],
	],
	'testShouldIgnoreEmptyTransientValue'                => [
		'config'   => [
			'initial_cdn_state' => 'rocketcdn_paid',
			'transient_value'   => false,
		],
		'expected' => [
			'cdn_state' => 'rocketcdn_paid',
		],
	],
	'testShouldIgnoreApiFallbackDefaultWithNon200Status' => [
		'config'   => [
			'initial_cdn_state' => 'nothing',
			'transient_value'   => [
				'plan_type'   => 'free',
				'status_code' => 500,
			],
		],
		'expected' => [
			'cdn_state' => 'nothing',
		],
	],
	'testShouldIgnoreMissingStatusCode'                  => [
		'config'   => [
			'initial_cdn_state' => 'rocketcdn_paid',
			'transient_value'   => [
				'plan_type' => 'free',
			],
		],
		'expected' => [
			'cdn_state' => 'rocketcdn_paid',
		],
	],
];
