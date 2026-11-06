<?php

return [
	'test_data' => [
		'shouldReturnDefaultParamsWhenImagifyNotActiveOrWhiteLabelIsFalse' => [
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
