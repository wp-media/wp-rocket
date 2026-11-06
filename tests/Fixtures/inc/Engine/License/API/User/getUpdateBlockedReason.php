<?php

return [
	'testShouldReturnEmptyStringWhenNotRevokedAndLicenseNotExpired' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'next year' ),
			'licence' => [
				'is_revoked' => false,
			],
		] ) ),
		'expected' => '',
	],
	'testShouldReturnBanReasonWhenRevokedWithReason' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'next year' ),
			'licence' => [
				'is_revoked'  => true,
				'plugin_updates_ban_reason' => 'BANNED_WEBSITE',
			],
		] ) ),
		'expected' => 'There was an error updating the plugin because your website is banned',
	],
	'testShouldReturnDefaultBanMessageWhenRevokedWithoutReason' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'next year' ),
			'licence' => [
				'is_revoked' => true,
			],
		] ) ),
		'expected' => 'There was an error updating the plugin.',
	],
	'testShouldReturnExpiredMessageWhenLicenseExpired' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'last year' ),
			'licence' => [
				'is_revoked' => false,
			],
		] ) ),
		'expected' => 'There was an error updating the plugin.',
	],
	'testShouldReturnBanReasonWhenBothRevokedAndExpired' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'last year' ),
			'licence' => [
				'is_revoked'  => true,
				'plugin_updates_ban_reason' => 'BANNED_WEBSITE',
			],
		] ) ),
		'expected' => 'There was an error updating the plugin because your website is banned',
	],
	'testShouldReturnEmptyStringWhenRevokedNotSet' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'next year' ),
		] ) ),
		'expected' => '',
	],
];
