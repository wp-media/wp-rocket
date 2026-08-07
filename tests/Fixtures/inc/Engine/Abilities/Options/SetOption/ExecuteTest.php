<?php

return [
	'testShouldReturnErrorWhenOptionNameInvalid'           => [
		'config'   => [
			'input'          => [
				'option_name'  => 'unknown_option',
				'option_value' => 1,
			],
			'previous_value' => null,
			'allowed_keys'   => [ 'cache_webp', 'minify_css' ],
		],
		'expected' => [
			'success' => false,
			'error'   => 'Invalid option name: unknown_option. This option cannot be set via the ability.',
		],
	],

	'testShouldReturnErrorWhenOptionRemovedFromAllowlist'  => [
		'config'   => [
			'input'          => [
				'option_name'  => 'analytics_enabled',
				'option_value' => 1,
			],
			'previous_value' => null,
			'allowed_keys'   => [ 'cache_webp', 'minify_css' ],
		],
		'expected' => [
			'success' => false,
			'error'   => 'Invalid option name: analytics_enabled. This option cannot be set via the ability.',
		],
	],

	'testShouldSanitizeBooleanOptionToOneWhenTruthy'       => [
		'config'   => [
			'input'          => [
				'option_name'  => 'cache_webp',
				'option_value' => true,
			],
			'previous_value' => 0,
			'allowed_keys'   => [ 'cache_webp' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => 0,
			'new_value'      => 1,
		],
	],

	'testShouldSanitizeBooleanOptionToOneWhenStringOne'    => [
		'config'   => [
			'input'          => [
				'option_name'  => 'minify_css',
				'option_value' => '1',
			],
			'previous_value' => 0,
			'allowed_keys'   => [ 'minify_css' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => 0,
			'new_value'      => 1,
		],
	],

	'testShouldSanitizeBooleanOptionToZeroWhenFalsy'       => [
		'config'   => [
			'input'          => [
				'option_name'  => 'cache_webp',
				'option_value' => false,
			],
			'previous_value' => 1,
			'allowed_keys'   => [ 'cache_webp' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => 1,
			'new_value'      => 0,
		],
	],

	'testShouldSanitizeBooleanOptionToZeroWhenEmptyString' => [
		'config'   => [
			'input'          => [
				'option_name'  => 'lazyload',
				'option_value' => '',
			],
			'previous_value' => 1,
			'allowed_keys'   => [ 'lazyload' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => 1,
			'new_value'      => 0,
		],
	],

	'testShouldSanitizeIntegerOption'                      => [
		'config'   => [
			'input'          => [
				'option_name'  => 'purge_cron_interval',
				'option_value' => '10',
			],
			'previous_value' => 5,
			'allowed_keys'   => [ 'purge_cron_interval' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => 5,
			'new_value'      => 10,
		],
	],

	'testShouldSanitizeIntegerOptionToZeroForNonNumeric'   => [
		'config'   => [
			'input'          => [
				'option_name'  => 'purge_cron_interval',
				'option_value' => 'not_a_number',
			],
			'previous_value' => 5,
			'allowed_keys'   => [ 'purge_cron_interval' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => 5,
			'new_value'      => 0,
		],
	],

	'testShouldAcceptValidEnumValue'                       => [
		'config'   => [
			'input'          => [
				'option_name'  => 'purge_cron_unit',
				'option_value' => 'HOUR_IN_SECONDS',
			],
			'previous_value' => 'MINUTE_IN_SECONDS',
			'allowed_keys'   => [ 'purge_cron_unit' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => 'MINUTE_IN_SECONDS',
			'new_value'      => 'HOUR_IN_SECONDS',
		],
	],

	'testShouldAcceptValidEnumValueForCleanupFrequency'    => [
		'config'   => [
			'input'          => [
				'option_name'  => 'automatic_cleanup_frequency',
				'option_value' => 'weekly',
			],
			'previous_value' => 'daily',
			'allowed_keys'   => [ 'automatic_cleanup_frequency' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => 'daily',
			'new_value'      => 'weekly',
		],
	],

	'testShouldRejectInvalidEnumValueAndKeepCurrent'       => [
		'config'   => [
			'input'          => [
				'option_name'  => 'purge_cron_unit',
				'option_value' => 'INVALID_VALUE',
			],
			'previous_value' => 'MINUTE_IN_SECONDS',
			'allowed_keys'   => [ 'purge_cron_unit' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => 'MINUTE_IN_SECONDS',
			'new_value'      => 'MINUTE_IN_SECONDS',
		],
	],

	'testShouldReturnEmptyArrayForNonArrayInputOnArrayOption' => [
		'config'   => [
			'input'          => [
				'option_name'  => 'cdn_cnames',
				'option_value' => 'not_an_array',
			],
			'previous_value' => [ 'existing.com' ],
			'allowed_keys'   => [ 'cdn_cnames' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => [ 'existing.com' ],
			'new_value'      => [],
		],
	],

	'testShouldMergeArrayOptionInUpdateModeByDefault'      => [
		'config'   => [
			'input'          => [
				'option_name'  => 'cdn_cnames',
				'option_value' => [ 'cdn2.example.com' ],
			],
			'previous_value' => [ 'cdn1.example.com' ],
			'allowed_keys'   => [ 'cdn_cnames' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => [ 'cdn1.example.com' ],
			'new_value'      => [ 'cdn1.example.com', 'cdn2.example.com' ],
		],
	],

	'testShouldMergeArrayOptionWhenUpdateModeIsExplicit'   => [
		'config'   => [
			'input'          => [
				'option_name'  => 'cdn_cnames',
				'option_value' => [ 'cdn2.example.com' ],
				'update_mode'  => 'update',
			],
			'previous_value' => [ 'cdn1.example.com' ],
			'allowed_keys'   => [ 'cdn_cnames' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => [ 'cdn1.example.com' ],
			'new_value'      => [ 'cdn1.example.com', 'cdn2.example.com' ],
		],
	],

	'testShouldReplaceArrayOptionWhenReplaceModeRequested' => [
		'config'   => [
			'input'          => [
				'option_name'  => 'cdn_cnames',
				'option_value' => [ 'cdn2.example.com' ],
				'update_mode'  => 'replace',
			],
			'previous_value' => [ 'cdn1.example.com' ],
			'allowed_keys'   => [ 'cdn_cnames' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => [ 'cdn1.example.com' ],
			'new_value'      => [ 'cdn2.example.com' ],
		],
	],

	'testShouldDeduplicateWhenMergingArrayOption'          => [
		'config'   => [
			'input'          => [
				'option_name'  => 'cdn_cnames',
				'option_value' => [ 'cdn1.example.com', 'cdn2.example.com' ],
			],
			'previous_value' => [ 'cdn1.example.com' ],
			'allowed_keys'   => [ 'cdn_cnames' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => [ 'cdn1.example.com' ],
			'new_value'      => [ 'cdn1.example.com', 'cdn2.example.com' ],
		],
	],

	'testShouldMergeTextareaOptionInUpdateModeByDefault'   => [
		'config'   => [
			'input'          => [
				'option_name'  => 'cache_reject_uri',
				'option_value' => [ '/new-page/' ],
			],
			'previous_value' => [ '/existing-page/' ],
			'allowed_keys'   => [ 'cache_reject_uri' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => [ '/existing-page/' ],
			'new_value'      => [ '/existing-page/', '/new-page/' ],
		],
	],

	'testShouldReplaceTextareaOptionWhenReplaceModeRequested' => [
		'config'   => [
			'input'          => [
				'option_name'  => 'cache_reject_uri',
				'option_value' => [ '/new-page/' ],
				'update_mode'  => 'replace',
			],
			'previous_value' => [ '/existing-page/' ],
			'allowed_keys'   => [ 'cache_reject_uri' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => [ '/existing-page/' ],
			'new_value'      => [ '/new-page/' ],
		],
	],

	'testShouldAcceptNewBooleanOptionsFromAllowlist'       => [
		'config'   => [
			'input'          => [
				'option_name'  => 'minify_google_fonts',
				'option_value' => 1,
			],
			'previous_value' => 0,
			'allowed_keys'   => [ 'minify_google_fonts' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => 0,
			'new_value'      => 1,
		],
	],

	'testShouldAcceptPerformanceMonitoringFrequencyAsInteger' => [
		'config'   => [
			'input'          => [
				'option_name'  => 'performance_monitoring_schedule_frequency',
				'option_value' => '86400',
			],
			'previous_value' => 604800,
			'allowed_keys'   => [ 'performance_monitoring_schedule_frequency' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => 604800,
			'new_value'      => 86400,
		],
	],

	'testShouldAcceptPreloadFontsAsArrayOption'            => [
		'config'   => [
			'input'          => [
				'option_name'  => 'preload_fonts',
				'option_value' => [ 'https://example.com/font.woff2' ],
			],
			'previous_value' => [],
			'allowed_keys'   => [ 'preload_fonts' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => [],
			'new_value'      => [ 'https://example.com/font.woff2' ],
		],
	],
];
