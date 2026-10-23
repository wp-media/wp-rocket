<?php

return [
	'shouldShowCardForFreePlanWhileCreationIsLoading' => [
		'config'   => [
			'loading'           => true,
			'subscription_data' => [
				'is_active'           => true,
				'plan_type'           => 'free',
				'subscription_status' => 'running',
			],
		],
		'expected' => true,
	],

	'shouldShowCardForFreePlanAfterCreationCompleted' => [
		'config'   => [
			'loading'           => false,
			'subscription_data' => [
				'is_active'           => true,
				'plan_type'           => 'free',
				'subscription_status' => 'running',
			],
		],
		'expected' => true,
	],

	'shouldHideCardForPaidRunningPlan'                => [
		'config'   => [
			'loading'           => false,
			'subscription_data' => [
				'is_active'           => true,
				'plan_type'           => 'paid',
				'subscription_status' => 'running',
			],
		],
		'expected' => false,
	],
];
