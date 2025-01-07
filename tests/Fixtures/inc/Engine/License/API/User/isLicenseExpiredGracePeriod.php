<?php

return [
	'testShouldReturnTrueWhenNotObject' => [
		'data'     => [],
		'expected' => true,
	],
	'testShouldReturnTrueWhenPropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'ID' => 1,
		] ) ),
		'expected' => true,
	],
	'testShouldReturnFalseWhenNotExpired' => [
		'data'     => json_decode( json_encode( [
			'ID'                 => 1,
			'licence_expiration' => strtotime( 'next week' ),
		] ) ),
		'expected' => false,
	],
	'testShouldReturnTrueWhenExpired' => [
		'data'     => json_decode( json_encode( [
			'ID'                 => 1,
			'licence_expiration' => strtotime( '3 weeks ago' ),
		] ) ),
		'expected' => true,
	],
];
