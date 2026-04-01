<?php

return [
	'test_data' => [
		'shouldReturnDefaultParamsWhenImagifyNotActive' => [
			'config' => [
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
	],
];
