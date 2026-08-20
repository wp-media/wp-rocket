<?php

return [
	'testShouldDowngradeProToFreeWhenPlanTypeIsFree' => [
		'config'   => [
			'initial_cdn_state' => 'rocketcdn_paid',
			'transient_value'   => [ 'plan_type' => 'free' ],
		],
		'expected' => [
			'cdn_state' => 'rocketcdn_free',
		],
	],
	'testShouldUpgradeFreeToProWhenPlanTypeIsPaid' => [
		'config'   => [
			'initial_cdn_state' => 'rocketcdn_free',
			'transient_value'   => [ 'plan_type' => 'paid' ],
		],
		'expected' => [
			'cdn_state' => 'rocketcdn_paid',
		],
	],
	'testShouldLeaveProUnchangedWhenPlanTypeStillPaid' => [
		'config'   => [
			'initial_cdn_state' => 'rocketcdn_paid',
			'transient_value'   => [ 'plan_type' => 'paid' ],
		],
		'expected' => [
			'cdn_state' => 'rocketcdn_paid',
		],
	],
	'testShouldNotTouchByocdnState' => [
		'config'   => [
			'initial_cdn_state' => 'byocdn',
			'transient_value'   => [ 'plan_type' => 'free' ],
		],
		'expected' => [
			'cdn_state' => 'byocdn',
		],
	],
	'testShouldNotActivateFromNothing' => [
		'config'   => [
			'initial_cdn_state' => 'nothing',
			'transient_value'   => [ 'plan_type' => 'paid' ],
		],
		'expected' => [
			'cdn_state' => 'nothing',
		],
	],
	'testShouldIgnoreEmptyTransientValue' => [
		'config'   => [
			'initial_cdn_state' => 'rocketcdn_paid',
			'transient_value'   => false,
		],
		'expected' => [
			'cdn_state' => 'rocketcdn_paid',
		],
	],
];
