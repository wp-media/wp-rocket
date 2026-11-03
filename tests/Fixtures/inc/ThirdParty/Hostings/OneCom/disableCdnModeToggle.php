<?php

return [
	'testShouldReturnDefaultFieldSettings' => [
		'config'   => [
			'onecom_performance_plugin_enabled' => true,
			'oc_cdn_enabled'                    => false,
			'sections'                          => [
				'cdn_section'            => [
					'is_forced_off' => false,
				],
				'rocketcdn_paid_section' => [
					'is_forced_off' => false,
				],
				'rocketcdn_free_section' => [
					'is_forced_off' => false,
				],
			],
		],
		'expected' => [
			'sections' => [
				'cdn_section'            => [
					'is_forced_off' => false,
				],
				'rocketcdn_paid_section' => [
					'is_forced_off' => false,
				],
				'rocketcdn_free_section' => [
					'is_forced_off' => false,
				],
			],
		],
	],
	'testShouldNotUpdateFieldSettingsWhenOnePluginIsDisabled' => [
		'config'   => [
			'onecom_performance_plugin_enabled' => false,
			'oc_cdn_enabled'                    => true,
			'sections'                          => [
				'cdn_section'            => [ 'is_forced_off' => false ],
				'rocketcdn_paid_section' => [ 'is_forced_off' => false ],
				'rocketcdn_free_section' => [ 'is_forced_off' => false ],
			],
		],
		'expected' => [
			'sections' => [
				'cdn_section'            => [ 'is_forced_off' => false ],
				'rocketcdn_paid_section' => [ 'is_forced_off' => false ],
				'rocketcdn_free_section' => [ 'is_forced_off' => false ],
			],
		],
	],
	'testShouldUpdateFieldSettings'        => [
		'config'   => [
			'onecom_performance_plugin_enabled' => true,
			'oc_cdn_enabled'                    => true,
			'sections'                          => [
				'cdn_section'            => [ 'is_forced_off' => false ],
				'rocketcdn_paid_section' => [ 'is_forced_off' => false ],
				'rocketcdn_free_section' => [ 'is_forced_off' => false ],
			],
		],
		'expected' => [
			'sections' => [
				'cdn_section'            => [ 'is_forced_off' => true ],
				'rocketcdn_paid_section' => [ 'is_forced_off' => true ],
				'rocketcdn_free_section' => [ 'is_forced_off' => true ],
			],
		],
	],
	'testShouldForceStatusIndicatorInactiveWhenOcCdnEnabled' => [
		'config'   => [
			'onecom_performance_plugin_enabled' => true,
			'oc_cdn_enabled'                    => true,
			'sections'                          => [
				'cdn_section'            => [
					'is_forced_off'     => false,
					'status_indicator'  => [
						'is_active' => true,
						'label'     => 'Your CDN is active on your website',
					],
				],
				'rocketcdn_paid_section' => [
					'is_forced_off'    => false,
					'status_indicator' => [
						'is_active' => true,
					],
				],
				'rocketcdn_free_section' => [
					'is_forced_off'    => false,
					'status_indicator' => [
						'is_active' => true,
					],
				],
			],
		],
		'expected' => [
			'sections' => [
				'cdn_section'            => [
					'is_forced_off'    => true,
					'status_indicator' => [
						'is_active' => false,
						'label'     => 'Your CDN is active on your website',
					],
				],
				'rocketcdn_paid_section' => [
					'is_forced_off'    => true,
					'status_indicator' => [
						'is_active' => false,
					],
				],
				'rocketcdn_free_section' => [
					'is_forced_off'    => true,
					'status_indicator' => [
						'is_active' => false,
					],
				],
			],
		],
	],
	'testShouldNotUpdateStatusIndicatorWhenOcCdnDisabled' => [
		'config'   => [
			'onecom_performance_plugin_enabled' => true,
			'oc_cdn_enabled'                    => false,
			'sections'                          => [
				'cdn_section'            => [
					'is_forced_off'    => false,
					'status_indicator' => [
						'is_active' => true,
					],
				],
				'rocketcdn_paid_section' => [
					'is_forced_off'    => false,
					'status_indicator' => [
						'is_active' => true,
					],
				],
				'rocketcdn_free_section' => [
					'is_forced_off'    => false,
					'status_indicator' => [
						'is_active' => true,
					],
				],
			],
		],
		'expected' => [
			'sections' => [
				'cdn_section'            => [
					'is_forced_off'    => false,
					'status_indicator' => [
						'is_active' => true,
					],
				],
				'rocketcdn_paid_section' => [
					'is_forced_off'    => false,
					'status_indicator' => [
						'is_active' => true,
					],
				],
				'rocketcdn_free_section' => [
					'is_forced_off'    => false,
					'status_indicator' => [
						'is_active' => true,
					],
				],
			],
		],
	],
	'testShouldNotAddStatusIndicatorKeyWhenMissing' => [
		'config'   => [
			'onecom_performance_plugin_enabled' => true,
			'oc_cdn_enabled'                    => true,
			'sections'                          => [
				'cdn_section'            => [ 'is_forced_off' => false ],
				'rocketcdn_paid_section' => [ 'is_forced_off' => false ],
				'rocketcdn_free_section' => [ 'is_forced_off' => false ],
			],
		],
		'expected' => [
			'sections' => [
				'cdn_section'            => [ 'is_forced_off' => true ],
				'rocketcdn_paid_section' => [ 'is_forced_off' => true ],
				'rocketcdn_free_section' => [ 'is_forced_off' => true ],
			],
		],
	],
];
