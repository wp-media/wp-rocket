<?php

return [
	'test_data' => [
		'shouldDoNothingWhenNoCapability' => [
			'config' => [
				'capability'                  => false,
				'factories'                   => true,
				'transient'                   => false,
				'performance_hints_transient' => false,
			],
			'expected' => false,
		],
		'shouldDoNothingWhenFactoriesIsEmpty' => [
			'config' => [
				'capability'                  => true,
				'factories'                   => false,
				'transient'                   => false,
				'performance_hints_transient' => false,
			],
			'expected' => false,
		],
		'shouldDoNothingWhenNoTransient' => [
			'config' => [
				'capability'                  => true,
				'factories'                   => true,
				'transient'                   => false,
				'performance_hints_transient' => false,
			],
			'expected' => false,
		],
		'shouldShowNoticeWhenTransient' => [
			'config' => [
				'capability'                  => true,
				'factories'                   => true,
				'transient'                   => time() + 3600,
				'performance_hints_transient' => false,
			],
			'expected' => true,
		],
	],
];
