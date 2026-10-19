<?php

return [
	'test_data' => [
		'shouldReturnDefaultParamsWhenCDNNotEnabledAndWhiteLabelNotActive' => [
			'config'   => [
				'white_label' => false,
				'params'      => [
					'enabled_options' => [ 'plugin_example' ],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [ 'plugin_example' ],
				],
			],
		],

		'shouldAddRocketCdnWhenCDNOptionIsEnabled' => [
			'config'   => [
				'white_label' => false,
				'params'      => [
					'enabled_options' => [ 'cdn' ],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [ 'cdn', 'plugin_rocketcdn' ],
				],
			],
		],

		'shouldAddRocketCdnWhenWhiteLabelIsActive' => [
			'config'   => [
				'white_label' => true,
				'params'      => [
					'enabled_options' => [ 'plugin_example' ],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [ 'plugin_example', 'plugin_rocketcdn' ],
				],
			],
		],
	],
];
