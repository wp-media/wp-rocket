<?php

return [
	'testShouldDoNothingWhenOptinDisabled' => [
		'config' => [
			'optin_enabled' => false,
			'old_value'     => [
				'auto_preload_fonts' => 0,
			],
			'value'         => [
				'auto_preload_fonts' => 1,
			],
		],
		'expected' => false,
	],
	'testShouldDoNothingWhenOptionNotSet' => [
		'config' => [
			'optin_enabled' => true,
			'old_value'     => [
				'auto_preload_fonts' => 0,
			],
			'value'         => [],
		],
		'expected' => false,
	],
	'testShouldDoNothingWhenOptionValueUnchanged' => [
		'config' => [
			'optin_enabled' => true,
			'old_value'     => [
				'auto_preload_fonts' => 0,
			],
			'value'         => [
				'auto_preload_fonts' => 0,
			],
		],
		'expected' => false,
	],
	'testShouldTrackOptionChange' => [
		'config' => [
			'optin_enabled' => true,
			'option_name'   => 'auto_preload_fonts',
			'old_value'     => [
				'auto_preload_fonts' => 0,
			],
			'value'         => [
				'auto_preload_fonts' => 1,
			],
		],
		'expected' => true,
	],
];
