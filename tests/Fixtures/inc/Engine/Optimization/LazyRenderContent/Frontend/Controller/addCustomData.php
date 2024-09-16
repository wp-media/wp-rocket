<?php

return [
	'testShouldReturnDataWithAllowed' => [
		'config'   => [
			'is_allowed' => true,
		],
		'data'     => [],
		'expected' => [
			'status'   => [
				'lrc' => true,
			],
			'lrc_threshold' => 1800
		],
	],
	'testShouldReturnDataWithNotAllowed' => [
		'config'   => [
			'is_allowed' => false,
		],
		'data'     => [],
		'expected' => [
			'status'   => [
				'lrc' => false,
			],
			'lrc_threshold' => 1800,
		],
	],
];
