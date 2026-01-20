<?php

return [
	'testShouldReturnEmptyStringWhenNotBannedAndLicenseNotExpired' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'next year' ),
			'licence' => [
				'is_banned' => false,
			],
		] ) ),
		'expected' => '',
	],
	'testShouldReturnBanReasonWhenBannedWithReason' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'next year' ),
			'licence' => [
				'is_banned'  => true,
				'ban_reason' => 'Payment failed',
			],
		] ) ),
		'expected' => 'There was an error updating the plugin because Payment failed',
	],
	'testShouldReturnDefaultBanMessageWhenBannedWithoutReason' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'next year' ),
			'licence' => [
				'is_banned' => true,
			],
		] ) ),
		'expected' => 'There was an error updating the plugin.',
	],
	'testShouldReturnExpiredMessageWhenLicenseExpired' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'last year' ),
			'licence' => [
				'is_banned' => false,
			],
		] ) ),
		'expected' => 'There was an error updating the plugin.',
	],
	'testShouldReturnBanReasonWhenBothBannedAndExpired' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'last year' ),
			'licence' => [
				'is_banned'  => true,
				'ban_reason' => 'Fraud detected',
			],
		] ) ),
		'expected' => 'There was an error updating the plugin because Fraud detected',
	],
	'testShouldReturnEmptyStringWhenBannedNotSet' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'next year' ),
		] ) ),
		'expected' => '',
	],
	'testShouldReturnErrorMessageWhenLicenseRevoked' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'next year' ),
			'licence' => [
				'is_banned'  => false,
				'is_revoked' => true,
			],
		] ) ),
		'expected' => 'There was an error updating the plugin.',
	],
];
