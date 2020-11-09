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
			'ID'           => 1,
			'date_created' => 12345,
		] ) ),
		'expected' => 12345,
	],
];
