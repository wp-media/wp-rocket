<?php

return [
	'test_data' => [
		'shouldReturnParamsUnchangedWhenNoCDNOptionAndNoWhiteLabelAndNoRocketcdnStandalone' => [
			'config'   => [
				'white_label'         => false,
				'is_reseller'         => false,
				'has_customer_data'   => false,
				'subscription_status' => 'cancelled',
				'params'              => [
					'enabled_options' => [ 'plugin_example' ],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [ 'plugin_example' ],
				],
			],
		],

		'shouldAddPluginRocketcdnWhenSubscriptionIsRunningAndPaidAndNotReseller' => [
			'config'   => [
				'white_label'         => false,
				'is_reseller'         => false,
				'has_customer_data'   => true,
				'subscription_status' => 'running',
				'plan_type'           => 'paid',
				'params'              => [
					'enabled_options' => [ 'cdn' ],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [ 'cdn', 'plugin_rocketcdn' ],
				],
			],
		],

		'shouldNotAddPluginRocketcdnWhenSubscriptionIsRunningAndPaidAndIsReseller' => [
			'config'   => [
				'white_label'         => false,
				'is_reseller'         => true,
				'has_customer_data'   => true,
				'subscription_status' => 'running',
				'plan_type'           => 'paid',
				'params'              => [
					'enabled_options' => [ 'cdn' ],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [ 'cdn' ],
				],
			],
		],

		'shouldAddFreeOptionWhenSubscriptionIsRunningAndFreeAndNotReseller' => [
			'config'   => [
				'white_label'         => false,
				'is_reseller'         => false,
				'has_customer_data'   => true,
				'subscription_status' => 'running',
				'plan_type'           => 'free',
				'params'              => [
					'enabled_options' => [ 'cdn' ],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [ 'cdn', 'plugin_rocketcdn_free' ],
				],
			],
		],

		'shouldAddFreeOptionWhenSubscriptionIsRunningAndFreeAndIsReseller' => [
			'config'   => [
				'white_label'         => false,
				'is_reseller'         => true,
				'has_customer_data'   => true,
				'subscription_status' => 'running',
				'plan_type'           => 'free',
				'params'              => [
					'enabled_options' => [ 'cdn' ],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [ 'cdn', 'plugin_rocketcdn_free' ],
				],
			],
		],

		'shouldReturnParamsUnchangedWhenSubscriptionIsNotRunning' => [
			'config'   => [
				'white_label'         => false,
				'is_reseller'         => false,
				'has_customer_data'   => true,
				'subscription_status' => 'cancelled',
				'plan_type'           => 'free',
				'params'              => [
					'enabled_options' => [ 'cdn' ],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [ 'cdn' ],
				],
			],
		],
	],
];
