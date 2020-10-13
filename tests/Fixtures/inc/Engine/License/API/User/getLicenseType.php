<?php

return [
	'testShouldReturnZeroWhenNotObject' => [
		'data'     => [],
		'expected' => 0,
	],
	'testShouldReturnZeroWhenPropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'ID' => 1,
		] ) ),
		'expected' => 0,
	],
	'testShouldReturnValueWhenPropertySet' => [
		'data'     => json_decode( json_encode( [
			'ID'              => 1,
			'licence_account' => 3,
		] ) ),
		'expected' => 3,
	],
];
