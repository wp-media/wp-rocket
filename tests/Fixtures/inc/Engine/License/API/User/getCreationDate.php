<?php

return [
	'testShouldReturnCurrentTimeWhenNotObject' => [
		'data'     => [],
		'expected' => 0,
	],
	'testShouldReturnCurrentTimeWhenPropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'ID' => 1,
		] ) ),
		'expected' => 0,
	],
	'testShouldReturnCurrentTimeWhenPropertyZero' => [
		'data' => json_decode( json_encode( [
			'ID' => 1,
			'date_created' => 0,
		] ) ),
		'expected' => 0,
	],
	'testShouldReturnValueWhenPropertySet' => [
		'data'     => json_decode( json_encode( [
			'ID'           => 1,
			'date_created' => 12345,
		] ) ),
		'expected' => 12345,
	],
];
