<?php

return [
	'testShouldReturnErrorWhenNoCapability' => [
		'config' => [
			'capability' => false,
			'value'      => '1',
		],
		'expected' => [
			'success'  => false,
			'message' => 'Missing capability',
		],
	],
	'testShouldReturnErrorWhenNoValue' => [
		'config' => [
			'capability' => true,
			'value'      => null,
		],
		'expected' => [
			'success'  => false,
			'message' => 'Missing value parameter',
		],
	],
	'testShouldEnableOptin' => [
		'config' => [
			'capability' => true,
			'value'      => '1',
			'sdk'        => '<script>window.mixpanel = [];</script>',
		],
		'expected' => [
			'success'  => true,
			'message' => 'Opt-in enabled.',
			'data'    => [
				'message' => 'Opt-in enabled.',
				'sdk'     => '<script>window.mixpanel = [];</script>',
			],
		],
	],
	'testShouldEnableOptinWithEmptySdk' => [
		'config' => [
			'capability' => true,
			'value'      => '1',
			'sdk'        => '',
		],
		'expected' => [
			'success'  => true,
			'message' => 'Opt-in enabled.',
			'data'    => [
				'message' => 'Opt-in enabled.',
				'sdk'     => '',
			],
		],
	],
	'testShouldDisableOptin' => [
		'config' => [
			'capability' => true,
			'value'      => '0',
		],
		'expected' => [
			'success'  => true,
			'message' => 'Opt-in disabled.',
			'data'    => [ 'message' => 'Opt-in disabled.' ],
		],
	],
];
