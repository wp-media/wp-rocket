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

		'shouldAddImagifytoRecommendationsApiParamsWhenImagifyActiveAndWhiteLabelEnabled' => [
			'config' => [
				'has_imagify_api_key'  => true,
				'white_label_account'  => true,
				'params'               => [
					'enabled_options' => [
						'minify_css',
						'minify_js',
						'manual_preload'
					],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [
						'minify_css',
						'minify_js',
						'manual_preload',
						'plugin_imagify',
					],
				],
			],
		],
	],
];
