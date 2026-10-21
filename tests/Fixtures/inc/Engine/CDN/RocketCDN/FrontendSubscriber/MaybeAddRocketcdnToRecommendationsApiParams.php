<?php

return [
	'test_data' => [
		'shouldReturnDefaultParamsWhenNoRocketCdnAndWhiteLabelNotActive' => [
			'config'   => [
				'white_label' => false,
				'cdn_state'   => 'nothing',
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

		'shouldReturnDefaultParamsWhenRocketCdnFree' => [
			'config'   => [
				'white_label' => false,
				'cdn_state'   => 'rocketcdn_free',
				'params'      => [
					'enabled_options' => [ 'cdn' ],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [ 'cdn' ],
				],
			],
		],

		'shouldReturnDefaultParamsWhenOtherCdn'      => [
			'config'   => [
				'white_label' => false,
				'cdn_state'   => 'byocdn',
				'params'      => [
					'enabled_options' => [ 'cdn' ],
				],
			],
			'expected' => [
				'params' => [
					'enabled_options' => [ 'cdn' ],
				],
			],
		],

		'shouldAddRocketCdnWhenRocketCdnPaid'        => [
			'config'   => [
				'white_label' => false,
				'cdn_state'   => 'rocketcdn_paid',
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

		'shouldAddRocketCdnWhenWhiteLabelIsActive'   => [
			'config'   => [
				'white_label' => true,
				'cdn_state'   => 'nothing',
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
