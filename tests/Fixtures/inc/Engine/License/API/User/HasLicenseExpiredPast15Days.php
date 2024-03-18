<?php

return [
	'testShouldReturnTrueWhenNotObject' => [
		'data'     => [],
		'expected' => true,
	],
	'testShouldReturnFalseWhenExpiredPast15Days' => [
		'data'     => json_decode( json_encode( [
			'ID'                 => 1,
			'licence_expiration' => strtotime( 'next week' ),
		] ) ),
		'expected' => false,
	],
	'testShouldReturnTrueWhenExpiredLessThan15Days' => [
		'data'     => json_decode( json_encode( [
			'ID'                 => 1,
			'licence_expiration' => strtotime( 'last week' ),
		] ) ),
		'expected' => true,
	],
];
