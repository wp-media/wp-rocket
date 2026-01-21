<?php

return [
	'testShouldReturnTrueWhenNotBannedAndLicenseNotExpired' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'next year' ),
			'licence' => [
				'is_banned' => false,
			],
		] ) ),
		'expected' => true,
	],
	'testShouldReturnFalseWhenBanned' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'next year' ),
			'licence' => [
				'is_banned' => true,
			],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenLicenseExpired' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'last year' ),
			'licence' => [
				'is_banned' => false,
			],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenBannedAndLicenseExpired' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'last year' ),
			'licence' => [
				'is_banned' => true,
			],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnTrueWhenBannedNotSet' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'next year' ),
		] ) ),
		'expected' => true,
	],
	'testShouldReturnFalseWhenLicenseRevoked' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'next year' ),
			'licence' => [
				'is_banned'  => false,
			],
		] ) ),
		'expected' => false,
	],
];
