<?php

return [
	'test_data' => [
		'shouldReturnDefaultParamsWhenNoCustomerData' => [
			'config' => [
				'has_customer_data'     => false,
				'subscription_status'   => 'running',
				'white_label'           => true,
				'params'                => [
					'enabled_options' => [ 'plugin_example' ],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [ 'plugin_example' ],
				],
			],
		],

		'shouldReturnDefaultParamsWhenSubscriptionNotRunning' => [
			'config' => [
				'has_customer_data'     => true,
				'subscription_status'   => 'cancelled',
				'white_label'           => true,
				'params'                => [
					'enabled_options' => [ 'plugin_example' ],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [ 'plugin_example' ],
				],
			],
		],

		'shouldReturnDefaultParamsWhenSubscriptionPending' => [
			'config' => [
				'has_customer_data'     => true,
				'subscription_status'   => 'pending',
				'white_label'           => true,
				'params'                => [
					'enabled_options' => [ 'plugin_example' ],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [ 'plugin_example' ],
				],
			],
		],

		'shouldReturnDefaultParamsWhenWhiteLabelNotActive' => [
			'config' => [
				'has_customer_data'     => true,
				'subscription_status'   => 'running',
				'white_label'           => false,
				'params'                => [
					'enabled_options' => [ 'plugin_example' ],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [ 'plugin_example' ],
				],
			],
		],

		'shouldReturnDefaultParamsWhenSubscriptionStatusNotSet' => [
			'config' => [
				'has_customer_data'     => true,
				'white_label'           => true,
				'params'                => [
					'enabled_options' => [ 'plugin_example' ],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [ 'plugin_example' ],
				],
			],
		],

		'shouldReturnDefaultParamsWithEmptyOptions' => [
			'config' => [
				'has_customer_data'     => false,
				'subscription_status'   => 'running',
				'white_label'           => true,
				'params'                => [
					'enabled_options' => [],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [],
				],
			],
		],
	],
];
