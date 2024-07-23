<?php

return [
	'testShoulDoNothingWhenDisabled' => [
		'config' => [
			'filter' => false,
			'not_completed' => 1,
		],
		'expected' => false,
	],
	'testShoulDoPartialWhenNotCompletedRows' => [
		'config' => [
			'filter' => true,
			'not_completed' => 1,
		],
		'expected' => 'partial',
	],
	'testShoulDoTruncate' => [
		'config' => [
			'filter' => true,
			'not_completed' => 0,
		],
		'expected' => 'truncate',
	],
];
