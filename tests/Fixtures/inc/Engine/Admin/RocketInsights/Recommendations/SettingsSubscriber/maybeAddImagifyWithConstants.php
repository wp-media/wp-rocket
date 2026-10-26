<?php

return [
	'test_data' => [
		'shouldAddImagifyWhenImagifyVersionIsDefined' => [
			'config'   => [
				'constants' => [
					'IMAGIFY_VERSION' => '2.2.5',
				],
				'params'    => [
					'enabled_options' => [ 'plugin_example' ],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [ 'plugin_example', 'plugin_imagify' ],
				],
			],
		],

		'shouldAddImagifyWhenWhiteLabelIsActive'      => [
			'config'   => [
				'constants' => [
					'WP_ROCKET_WHITE_LABEL_ACCOUNT' => true,
				],
				'params'    => [
					'enabled_options' => [ 'plugin_example' ],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [ 'plugin_example', 'plugin_imagify' ],
				],
			],
		],
	],
];
