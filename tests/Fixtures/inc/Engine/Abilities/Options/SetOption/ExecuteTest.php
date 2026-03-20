<?php

return [
	'testShouldReturnErrorWhenInputIsNull' => [
		'config'   => [
			'input'          => null,
			'previous_value' => null,
		],
		'expected' => [
			'success' => false,
			'error'   => 'Missing required parameters: option_name and option_value',
		],
	],

	'testShouldReturnErrorWhenInputIsNotArray' => [
		'config'   => [
			'input'          => 'string_input',
			'previous_value' => null,
		],
		'expected' => [
			'success' => false,
			'error'   => 'Missing required parameters: option_name and option_value',
		],
	],

	'testShouldReturnErrorWhenInputMissingOptionName' => [
		'config'   => [
			'input'          => [
				'option_value' => 1,
			],
			'previous_value' => null,
		],
		'expected' => [
			'success' => false,
			'error'   => 'Missing required parameters: option_name and option_value',
		],
	],

	'testShouldReturnErrorWhenInputMissingOptionValue' => [
		'config'   => [
			'input'          => [
				'option_name' => 'cache_webp',
			],
			'previous_value' => null,
		],
		'expected' => [
			'success' => false,
			'error'   => 'Missing required parameters: option_name and option_value',
		],
	],

	'testShouldReturnErrorWhenOptionNameInvalid' => [
		'config'   => [
			'input'          => [
				'option_name'  => 'unknown_option',
				'option_value' => 1,
			],
			'previous_value' => null,
		],
		'expected' => [
			'success' => false,
			'error'   => 'Invalid option name: unknown_option. This option cannot be set via the ability.',
		],
	],

	'testShouldSanitizeBooleanOptionToOneWhenTruthy' => [
		'config'   => [
			'input'          => [
				'option_name'  => 'cache_webp',
				'option_value' => true,
			],
			'previous_value' => 0,
		],
		'expected' => [
			'success'        => true,
			'previous_value' => 0,
			'new_value'      => 1,
		],
	],

	'testShouldSanitizeBooleanOptionToOneWhenStringOne' => [
		'config'   => [
			'input'          => [
				'option_name'  => 'minify_css',
				'option_value' => '1',
			],
			'previous_value' => 0,
		],
		'expected' => [
			'success'        => true,
			'previous_value' => 0,
			'new_value'      => 1,
		],
	],

	'testShouldSanitizeBooleanOptionToZeroWhenFalsy' => [
		'config'   => [
			'input'          => [
				'option_name'  => 'cache_webp',
				'option_value' => false,
			],
			'previous_value' => 1,
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
		],
		'expected' => [
			'success'        => true,
			'previous_value' => 1,
			'new_value'      => 0,
		],
	],

	'testShouldSanitizeIntegerOption' => [
		'config'   => [
			'input'          => [
				'option_name'  => 'purge_cron_interval',
				'option_value' => '10',
			],
			'previous_value' => 5,
		],
		'expected' => [
			'success'        => true,
			'previous_value' => 5,
			'new_value'      => 10,
		],
	],

	'testShouldSanitizeIntegerOptionToZeroForNonNumeric' => [
		'config'   => [
			'input'          => [
				'option_name'  => 'purge_cron_interval',
				'option_value' => 'not_a_number',
			],
			'previous_value' => 5,
		],
		'expected' => [
			'success'        => true,
			'previous_value' => 5,
			'new_value'      => 0,
		],
	],

	'testShouldAcceptValidEnumValue' => [
		'config'   => [
			'input'          => [
				'option_name'  => 'purge_cron_unit',
				'option_value' => 'HOUR_IN_SECONDS',
			],
			'previous_value' => 'MINUTE_IN_SECONDS',
		],
		'expected' => [
			'success'        => true,
			'previous_value' => 'MINUTE_IN_SECONDS',
			'new_value'      => 'HOUR_IN_SECONDS',
		],
	],

	'testShouldAcceptValidEnumValueForCleanupFrequency' => [
		'config'   => [
			'input'          => [
				'option_name'  => 'automatic_cleanup_frequency',
				'option_value' => 'weekly',
			],
			'previous_value' => 'daily',
		],
		'expected' => [
			'success'        => true,
			'previous_value' => 'daily',
			'new_value'      => 'weekly',
		],
	],

	'testShouldRejectInvalidEnumValueAndKeepCurrent' => [
		'config'   => [
			'input'          => [
				'option_name'  => 'purge_cron_unit',
				'option_value' => 'INVALID_VALUE',
			],
			'previous_value' => 'MINUTE_IN_SECONDS',
		],
		'expected' => [
			'success'        => true,
			'previous_value' => 'MINUTE_IN_SECONDS',
			'new_value'      => 'MINUTE_IN_SECONDS',
		],
	],

	'testShouldSanitizeStringOptionByStrippingAllTags' => [
		'config'   => [
			'input'          => [
				'option_name'  => 'critical_css',
				'option_value' => '<script>alert("xss")</script><style>body{color:red}</style>',
			],
			'previous_value' => '',
		],
		'expected' => [
			'success'        => true,
			'previous_value' => '',
			'new_value'      => 'body{color:red}',
		],
	],

	'testShouldReturnEmptyArrayForNonArrayInputOnArrayOption' => [
		'config'   => [
			'input'          => [
				'option_name'  => 'cdn_cnames',
				'option_value' => 'not_an_array',
			],
			'previous_value' => [ 'existing.com' ],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => [ 'existing.com' ],
			'new_value'      => [],
		],
	],

	'testShouldHandleDelayJsExclusionsSelected' => [
		'config'   => [
			'input'          => [
				'option_name'  => 'delay_js_exclusions_selected',
				'option_value' => [ 'woocommerce', 'elementor' ],
			],
			'previous_value' => [],
		],
		'expected' => [
			'success'        => true,
			'previous_value' => [],
			'new_value'      => [ 'woocommerce', 'elementor' ],
		],
	],
];
