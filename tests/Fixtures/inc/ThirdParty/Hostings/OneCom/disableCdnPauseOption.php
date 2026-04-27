<?php

return [
	'testShouldReturnDefaultFieldSettings' => [
		'config'   => [
			'onecom_performance_plugin_enabled' => true,
			'oc_cdn_enabled'                    => false,
			'sections'                          => [
				'cdn_section'            => [
					'status_indicator_data' => [
						'disable_pause_btn' => false,
					],
				],
				'rocketcdn_paid_section' => [
					'status_indicator_data' => [
						'disable_pause_btn' => false,
					],
				],
				'rocketcdn_free_section' => [
					'status_indicator_data' => [
						'disable_pause_btn' => false,
					],
				],
			],
		],
		'expected' => [
			'sections' => [
				'cdn_section'            => [
					'status_indicator_data' => [
						'disable_pause_btn' => false,
					],
				],
				'rocketcdn_paid_section' => [
					'status_indicator_data' => [
						'disable_pause_btn' => false,
					],
				],
				'rocketcdn_free_section' => [
					'status_indicator_data' => [
						'disable_pause_btn' => false,
					],
				],
			],
		],
	],
	'testShouldNotUpdateFieldSettingsWhenOnePluginIsDisabled' => [
		'config'   => [
			'onecom_performance_plugin_enabled' => false,
			'oc_cdn_enabled'                    => true,
			'sections'                          => [
				'cdn_section'            => [ 'status_indicator_data' => [ 'disable_pause_btn' => false ] ],
				'rocketcdn_paid_section' => [ 'status_indicator_data' => [ 'disable_pause_btn' => false ] ],
				'rocketcdn_free_section' => [ 'status_indicator_data' => [ 'disable_pause_btn' => false ] ],
			],
		],
		'expected' => [
			'sections' => [
				'cdn_section'            => [ 'status_indicator_data' => [ 'disable_pause_btn' => false ] ],
				'rocketcdn_paid_section' => [ 'status_indicator_data' => [ 'disable_pause_btn' => false ] ],
				'rocketcdn_free_section' => [ 'status_indicator_data' => [ 'disable_pause_btn' => false ] ],
			],
		],
	],
	'testShouldUpdateFieldSettings'                          => [
		'config'   => [
			'onecom_performance_plugin_enabled' => true,
			'oc_cdn_enabled'                    => true,
			'sections'                          => [
				'cdn_section'            => [ 'status_indicator_data' => [ 'disable_pause_btn' => false ] ],
				'rocketcdn_paid_section' => [ 'status_indicator_data' => [ 'disable_pause_btn' => false ] ],
				'rocketcdn_free_section' => [ 'status_indicator_data' => [ 'disable_pause_btn' => false ] ],
			],
		],
		'expected' => [
			'sections' => [
				'cdn_section'            => [ 'status_indicator_data' => [ 'disable_pause_btn' => true ] ],
				'rocketcdn_paid_section' => [ 'status_indicator_data' => [ 'disable_pause_btn' => true ] ],
				'rocketcdn_free_section' => [ 'status_indicator_data' => [ 'disable_pause_btn' => true ] ],
			],
		],
	],
];

