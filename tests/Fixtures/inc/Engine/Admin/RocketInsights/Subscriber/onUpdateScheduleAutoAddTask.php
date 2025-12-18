<?php

return [
	'test_data' => [
		'shouldScheduleWhenUpgradingFromOldVersionNoUrls' => [
			'config'   => [
				'old_version'   => '3.20.2',
				'new_version'   => '3.20.3',
				'options'       => [
					'wp_rocket_settings' => [
						'rocket_insights' => 1,
					],
				],
				'license_data'  => [
					'license_expiration' => time() + DAY_IN_SECONDS,
				],
				'existing_urls' => [],
			],
			'expected' => [
				'scheduled' => true,
			],
		],

		'shouldNotScheduleWhenUpgradingFromNewVersion' => [
			'config'   => [
				'old_version'   => '3.20.3',
				'new_version'   => '3.20.4',
				'options'       => [
					'wp_rocket_settings' => [
						'rocket_insights' => 1,
					],
				],
				'license_data'  => [
					'license_expiration' => time() + DAY_IN_SECONDS,
				],
				'existing_urls' => [],
			],
			'expected' => [
				'scheduled' => false,
			],
		],

		'shouldNotScheduleWhenRocketInsightsDisabled' => [
			'config'   => [
				'old_version'   => '3.20.2',
				'new_version'   => '3.20.3',
				'options'       => [
					'wp_rocket_settings' => [
						'rocket_insights' => 0,
					],
				],
				'license_data'  => [
					'license_expiration' => time() + DAY_IN_SECONDS,
				],
				'existing_urls' => [],
			],
			'expected' => [
				'scheduled' => false,
			],
		],

		'shouldNotScheduleWhenUrlsAlreadyExist' => [
			'config'   => [
				'old_version'   => '3.20.2',
				'new_version'   => '3.20.3',
				'options'       => [
					'wp_rocket_settings' => [
						'rocket_insights' => 1,
					],
				],
				'license_data'  => [
					'license_expiration' => time() + DAY_IN_SECONDS,
				],
				'existing_urls' => [
					[ 'url' => 'https://example.com', 'is_mobile' => 0 ],
				],
			],
			'expected' => [
				'scheduled' => false,
			],
		],

		'shouldScheduleWhenUpgradingFromEvenOlderVersion' => [
			'config'   => [
				'old_version'   => '3.19.0',
				'new_version'   => '3.20.3',
				'options'       => [
					'wp_rocket_settings' => [
						'rocket_insights' => 1,
					],
				],
				'license_data'  => [
					'license_expiration' => time() + DAY_IN_SECONDS,
				],
				'existing_urls' => [],
			],
			'expected' => [
				'scheduled' => true,
			],
		],
	],
];
