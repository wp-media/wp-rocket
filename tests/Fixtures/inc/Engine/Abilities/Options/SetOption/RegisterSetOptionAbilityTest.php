<?php

return [
	'settings'  => [
		'cache_webp'      => 0,
		'minify_css'      => 1,
		'purge_cron_unit' => 'MINUTE_IN_SECONDS',
		'cdn_cnames'      => [],
		'lazyload'        => 0,
	],
	'test_data' => [
		'testShouldReturnErrorWhenUserLacksPermission'    => [
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

		'testShouldReturnErrorForMissingParameters'       => [
			'config'   => [
				'has_permission' => true,
				'input'          => null,
			],
			'expected' => [
				'is_error' => true,
			],
		],

		'testShouldReturnErrorForInvalidOptionName'       => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'option_name'  => 'invalid_option_name',
					'option_value' => 1,
				],
			],
			'expected' => [
				'is_error' => true,
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

		'testShouldSetEnumOptionWhenValid'                => [
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

		'testShouldRejectCriticalCssAsBlockedOption'      => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'option_name'  => 'critical_css',
					'option_value' => 'body{color:red}',
				],
			],
			'expected' => [
				'is_error' => true,
			],
		],

		'testShouldSetArrayOptionWhenValid'               => [
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

		'testShouldReturnEmptyArrayForNonArrayInput'      => [
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

		'testShouldMergeArrayOptionByDefault'             => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'option_name'  => 'cdn_cnames',
					'option_value' => [ 'cdn2.example.com' ],
				],
			],
			'expected' => [
				'is_error'       => false,
				'success'        => true,
				'previous_value' => [],
				'new_value'      => [ 'cdn2.example.com' ],
			],
		],

		'testShouldReplaceArrayOptionWhenReplaceModeRequested' => [
			'config'   => [
				'has_permission' => true,
				'input'          => [
					'option_name'  => 'cdn_cnames',
					'option_value' => [ 'cdn2.example.com' ],
					'update_mode'  => 'replace',
				],
			],
			'expected' => [
				'is_error'       => false,
				'success'        => true,
				'previous_value' => [],
				'new_value'      => [ 'cdn2.example.com' ],
			],
		],

		'testShouldNotBeAdminContext'                     => [
			'config'   => [
				'action' => 'none',
			],
			'expected' => [
				'is_admin' => false,
			],
		],

		'testShouldHaveRocketAfterSaveOptionsHookedOutsideAdmin' => [
			'config'   => [
				'action' => 'none',
			],
			'expected' => [
				'hooked' => true,
			],
		],

		'testShouldFireOptionsChangedWhenUpdateRocketOptionAlone' => [
			'config'   => [
				'action'       => 'update_rocket_option',
				'option_name'  => 'lazyload',
				'option_value' => 1,
			],
			'expected' => [
				'options_changed_fired' => true,
			],
		],

		'testShouldFireOptionsChangedWhenSetOptionExecutes' => [
			'config'   => [
				'action'       => 'set_option_execute',
				'option_name'  => 'lazyload',
				'option_value' => 1,
			],
			'expected' => [
				'success'               => true,
				'options_changed_fired' => true,
			],
		],
	],
];
