<?php

return [
	'testShouldReturnCurrentTimeWhenNotObject' => [
		'data'     => [],
		'expected' => time(),
	],
	'testShouldReturnCurrentTimeWhenPropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'ID' => 1,
		] ) ),
		'expected' => time(),
	],
	'testShouldReturnCurrentTimeWhenPropertyZero' => [
		'data' => json_decode( json_encode( [
			'ID' => 1,
			'date_created' => 0,
		] ) ),
		'expected' => time(),
	],
	'testShouldReturnValueWhenPropertySet' => [
		'data'     => json_decode( json_encode( [
			'ID'           => 1,
			'date_created' => 12345,
		] ) ),
		'expected' => 12345,
	],
];
