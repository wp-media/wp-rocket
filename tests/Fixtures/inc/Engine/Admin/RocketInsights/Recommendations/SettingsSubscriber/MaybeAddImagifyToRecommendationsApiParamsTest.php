<?php

return [
	'test_data' => [
		'shouldReturnDefaultParamsWhenImagifyNotActive' => [
			'config' => [
				'has_imagify_api_key'  => false,
				'white_label_account'  => true,
				'params'               => [
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
				'has_imagify_api_key'  => false,
				'white_label_account'  => false,
				'params'               => [
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
				'has_imagify_api_key'  => false,
				'white_label_account'  => true,
				'params'               => [
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
