<?php

return [
	'settings'  => [
		'cache_webp'        => 0,
		'minify_css'        => 1,
		'purge_cron_unit'   => 'MINUTE_IN_SECONDS',
		'critical_css'      => '',
		'cdn_cnames'        => [],
	],
	'test_data' => [
		'testShouldReturnErrorWhenUserLacksPermission' => [
			'config'   => [
				'has_permission' => false,
				'input'          => [
					'option_name'  => 'cache_webp',
					'option_value' => 1,
				],
			],
			'expected' => [
				'is_error' => true,
			],
		],

		'testShouldReturnErrorForMissingParameters' => [
			'config'   => [
				'has_permission' => true,
				'input'          => null,
			],
			'expected' => [
				'is_error' => true,
			],
		],

		'testShouldReturnErrorForInvalidOptionName' => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'option_name'  => 'invalid_option_name',
					'option_value' => 1,
				],
			],
			'expected' => [
				'is_error' => false,
				'success'  => false,
				'error'    => 'Invalid option name: invalid_option_name. This option cannot be set via the ability.',
			],
		],

		'testShouldSetBooleanOptionWhenUserHasPermission' => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'option_name'  => 'cache_webp',
					'option_value' => 1,
				],
			],
			'expected' => [
				'is_error'       => false,
				'success'        => true,
				'previous_value' => 0,
				'new_value'      => 1,
			],
		],

		'testShouldSetEnumOptionWhenValid' => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'option_name'  => 'purge_cron_unit',
					'option_value' => 'HOUR_IN_SECONDS',
				],
			],
			'expected' => [
				'is_error'       => false,
				'success'        => true,
				'previous_value' => 'MINUTE_IN_SECONDS',
				'new_value'      => 'HOUR_IN_SECONDS',
			],
		],

		'testShouldSanitizeCriticalCssOption' => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'option_name'  => 'critical_css',
					'option_value' => '<style>body{margin:0}</style>',
				],
			],
			'expected' => [
				'is_error'       => false,
				'success'        => true,
				'previous_value' => '',
				'new_value'      => 'body{margin:0}',
			],
		],

		'testShouldSetArrayOptionWhenValid' => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'option_name'  => 'cdn_cnames',
					'option_value' => [ 'cdn.example.com' ],
				],
			],
			'expected' => [
				'is_error'       => false,
				'success'        => true,
				'previous_value' => [],
				'new_value'      => [ 'cdn.example.com' ],
			],
		],

		'testShouldReturnEmptyArrayForNonArrayInput' => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'option_name'  => 'cdn_cnames',
					'option_value' => 'not_an_array',
				],
			],
			'expected' => [
				'is_error'       => false,
				'success'        => true,
				'previous_value' => [],
				'new_value'      => [],
			],
		],
	],
];
