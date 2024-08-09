<?php

return [
	'test_data' => [
		'shouldDoNothingWhenNoCapability' => [
			'config' => [
				'capability'                  => false,
				'atf_context'                 => 1,
				'transient'                   => false,
				'performance_hints_transient' => false,
			],
			'expected' => false,
		],
		'shouldDoNothingWhenATFDisabled' => [
			'config' => [
				'capability'                  => true,
				'atf_context'                 => 0,
				'transient'                   => false,
				'performance_hints_transient' => false,
			],
			'expected' => false,
		],
		'shouldDoNothingWhenTransientTimeLessThanCurrentTime' => [
			'config' => [
				'capability'                  => true,
				'atf_context'                 => 0,
				'transient'                   => time() - 30,
				'performance_hints_transient' => false,
			],
			'expected' => false,
		],
		'shouldDoNothingWhenNoTransient' => [
			'config' => [
				'capability'                  => true,
				'atf_context'                 => 1,
				'transient'                   => false,
				'performance_hints_transient' => false,
			],
			'expected' => false,
		],
		'shouldShowNoticeWhenTransient' => [
			'config' => [
				'capability'                  => true,
				'atf_context'                 => 1,
				'transient'                   => time() + 3600,
				'performance_hints_transient' => false,
			],
			'expected' => true,
		],
	],
];
