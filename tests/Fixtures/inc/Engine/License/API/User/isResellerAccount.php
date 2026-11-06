<?php

return [
	'testShouldReturnFalseWhenNotObject' => [
		'data'     => [],
		'expected' => false,
	],
	'testShouldReturnFalseWhenPropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'ID' => 1,
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenNotReseller' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'is_reseller' => false,
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenResellerIsZero' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'is_reseller' => 0,
		] ) ),
		'expected' => false,
	],
	'testShouldReturnTrueWhenReseller' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'is_reseller' => true,
		] ) ),
		'expected' => true,
	],
	'testShouldReturnTrueWhenResellerIsOne' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'is_reseller' => 1,
		] ) ),
		'expected' => true,
	],
];