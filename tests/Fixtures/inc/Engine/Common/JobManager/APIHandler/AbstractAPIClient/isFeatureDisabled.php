<?php

return [
	'test_data' => [
		'testEmptyKeyShouldReturnFalseWithoutReadingTransient' => [
			'config'   => [
				'client'  => 'empty_key',
				'offset'  => null,
				'deleted' => false,
			],
			'expected' => false,
		],
		'testNoTransientShouldReturnFalse' => [
			'config'   => [
				'client'  => 'rucss',
				'offset'  => null,
				'deleted' => false,
			],
			'expected' => false,
		],
		'testFutureUntilShouldReturnTrue' => [
			'config'   => [
				'client'  => 'rucss',
				'offset'  => 120,
				'deleted' => false,
			],
			'expected' => true,
		],
		'testPastUntilShouldReturnFalse' => [
			'config'   => [
				'client'  => 'rucss',
				'offset'  => -10,
				'deleted' => false,
			],
			'expected' => false,
		],
		'testUntilInsideClockSlackShouldReturnTrue' => [
			'config'   => [
				'client'  => 'rucss',
				'offset'  => 3630,
				'deleted' => false,
			],
			'expected' => true,
		],
		'testUntilBeyondCapShouldDeleteTransientAndReturnFalse' => [
			'config'   => [
				'client'  => 'rucss',
				'offset'  => 3700,
				'deleted' => true,
			],
			'expected' => false,
		],
	],
];
