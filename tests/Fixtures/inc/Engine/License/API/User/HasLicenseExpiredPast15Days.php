<?php

return [
	'testShouldReturnFalseWhenExpiredPast15Days' => [
		'data'     => json_decode( json_encode( [
			'ID'                 => 1,
			'licence_expiration' => strtotime( '16 days' ),
			'expired_license' 	 => true,
		] ) ),
		'expected' => false,
	],
	'testShouldReturnTrueWhenExpiredLessThan15Days' => [
		'data'     => json_decode( json_encode( [
			'ID'                 => 1,
			'licence_expiration' => strtotime( '1 day' ),
			'expired_license' 	 => false,
		] ) ),
		'expected' => true,
	],
];
