<?php

return [
	'test_data' => [
		'shouldReturnTrueWhenTestsExist' => [
			'config'   => [
				'count' => 5,
			],
			'expected' => true,
		],

		'shouldReturnTrueWhenOneTestExists' => [
			'config'   => [
				'count' => 1,
			],
			'expected' => true,
		],

		'shouldReturnFalseWhenNoTests' => [
			'config'   => [
				'count' => 0,
			],
			'expected' => false,
		],

		'shouldHandleStringCount' => [
			'config'   => [
				'count' => '3',
			],
			'expected' => true,
		],
	],
];
