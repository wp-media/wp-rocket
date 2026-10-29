<?php

return [
	'testShouldReturnTrueWhenNotObject' => [
		'data'     => [],
		'expected' => true,
	],
	'testShouldReturnTrueWhenFalseGiven' => [
		'data'     => false,
		'expected' => true,
	],
	'testShouldReturnTrueWhenPropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'ID' => 1,
		] ) ),
		'expected' => true,
	],
	'testShouldReturnTrueWhenNull' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'can_use_ai' => null,
		] ) ),
		'expected' => true,
	],
	'testShouldReturnTrueWhenTrue' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'can_use_ai' => true,
		] ) ),
		'expected' => true,
	],
	'testShouldReturnFalseWhenFalse' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'can_use_ai' => false,
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenStringFalse' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'can_use_ai' => 'false',
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenStringZero' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'can_use_ai' => '0',
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenZero' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'can_use_ai' => 0,
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenStringTrue' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'can_use_ai' => 'true',
		] ) ),
		'expected' => false,
	],
];
