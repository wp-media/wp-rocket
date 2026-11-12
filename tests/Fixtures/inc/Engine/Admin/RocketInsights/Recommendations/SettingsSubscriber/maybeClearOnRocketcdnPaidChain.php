<?php

return [
	'shouldClearWhenFreeUpgradesToPaid' => [
		'config'   => [
			'initial_cdn_state' => 'rocketcdn_free',
			'role'              => 'administrator',
			'transient_value'   => [
				'plan_type'   => 'paid',
				'status_code' => 200,
				'success'     => true,
			],
		],
		'expected' => [
			'cdn_state'         => 'rocketcdn_paid',
			'transient_cleared' => true,
		],
	],
	'shouldClearWhenNothingUpgradesToPaid' => [
		'config'   => [
			'initial_cdn_state' => 'nothing',
			'role'              => 'administrator',
			'transient_value'   => [
				'plan_type'   => 'paid',
				'status_code' => 200,
				'success'     => true,
			],
		],
		'expected' => [
			'cdn_state'         => 'rocketcdn_paid',
			'transient_cleared' => true,
		],
	],
	'shouldKeepWhenPlanTypeIsFree' => [
		'config'   => [
			'initial_cdn_state' => 'nothing',
			'role'              => 'administrator',
			'transient_value'   => [
				'plan_type'   => 'free',
				'status_code' => 200,
				'success'     => true,
			],
		],
		'expected' => [
			'cdn_state'         => 'rocketcdn_free',
			'transient_cleared' => false,
		],
	],
	'shouldKeepWhenByocdn' => [
		'config'   => [
			'initial_cdn_state' => 'byocdn',
			'role'              => 'administrator',
			'transient_value'   => [
				'plan_type'   => 'paid',
				'status_code' => 200,
				'success'     => true,
			],
		],
		'expected' => [
			'cdn_state'         => 'byocdn',
			'transient_cleared' => false,
		],
	],
	'shouldKeepWhenUserLacksCapability' => [
		'config'   => [
			'initial_cdn_state' => 'rocketcdn_free',
			'role'              => 'subscriber',
			'transient_value'   => [
				'plan_type'   => 'paid',
				'status_code' => 200,
				'success'     => true,
			],
		],
		'expected' => [
			'cdn_state'         => 'rocketcdn_free',
			'transient_cleared' => false,
		],
	],
];
