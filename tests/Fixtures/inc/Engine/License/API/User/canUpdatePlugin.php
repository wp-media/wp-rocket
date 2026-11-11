<?php

return [
	'testShouldReturnTrueWhenNotRevokedAndLicenseNotExpired' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'next year' ),
			'licence' => [
				'is_revoked' => false,
			],
		] ) ),
		'expected' => true,
	],
	'testShouldReturnFalseWhenRevoked' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'next year' ),
			'licence' => [
				'is_revoked' => true,
			],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenLicenseExpired' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'last year' ),
			'licence' => [
				'is_revoked' => false,
			],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenRevokedAndLicenseExpired' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'last year' ),
			'licence' => [
				'is_revoked' => true,
			],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnTrueWhenRevokedNotSet' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'next year' ),
		] ) ),
		'expected' => true,
	],
];
