<?php

return [
	'testShouldReturnTrueWhenExpiredPast15Days' => [
		'data'     => json_decode( json_encode( [
			'ID'                 => 1,
			'licence_expiration' => strtotime( '-16 days' ),
		] ) ),
		'expected' => true,
	],
	'testShouldReturnFalseWhenExpiredLessThan15Days' => [
		'data'     => json_decode( json_encode( [
			'ID'                 => 1,
			'licence_expiration' => strtotime( '-1 day' ),
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenLicenceHasNotExpired' => [
		'data'     => json_decode( json_encode( [
			'ID'                 => 1,
			'licence_expiration' => strtotime( '10 day' ),
		] ) ),
		'expected' => false,
	],
];
