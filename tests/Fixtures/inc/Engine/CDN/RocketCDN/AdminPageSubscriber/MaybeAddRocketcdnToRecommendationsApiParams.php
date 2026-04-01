<?php

return [
	'test_data' => [
		'shouldReturnDefaultParamsWhenNoCustomerDataORSubscriptionNotRunningORWhiteLabelNotActive' => [
			'config' => [
				'has_customer_data'     => false,
				'subscription_status'   => 'cancelled',
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

		'shouldAddRocketCdnToRecommendationsApiParamsWhenCustomerDataAvailable' => [
			'config' => [
				'has_customer_data'     => true,
				'subscription_status'   => 'cancelled',
				'white_label'           => false,
				'params'                => [
					'enabled_options' => [],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [
						'plugin_rocketcdn',
					],
				],
			],
		],

		'shouldAddRocketCdnToRecommendationsApiParamsWhenSubscriptionIsAvailable' => [
			'config' => [
				'has_customer_data'     => false,
				'subscription_status'   => 'running',
				'white_label'           => false,
				'params'                => [
					'enabled_options' => [],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [
						'plugin_rocketcdn',
					],
				],
			],
		],
	],
];
