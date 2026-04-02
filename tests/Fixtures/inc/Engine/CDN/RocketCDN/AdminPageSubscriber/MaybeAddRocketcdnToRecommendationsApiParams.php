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

		'shouldAddRocketCdnToRecommendationsApiParamsWhenCDNOptionIsEnabled' => [
			'config' => [
				'has_customer_data'     => true,
				'subscription_status'   => 'cancelled',
				'white_label'           => false,
				'params'                => [
					'enabled_options' => [
						'cdn',
					],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [
						'cdn',
						'plugin_rocketcdn',
					],
				],
			],
		],
	],
];
