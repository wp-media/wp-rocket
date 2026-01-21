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
				'plugin_updates_ban_reason' => 'BANNED_WEBSITE',
			],
		] ) ),
		'expected' => 'There was an error updating the plugin because your website is banned',
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
				'plugin_updates_ban_reason' => 'BANNED_WEBSITE',
			],
		] ) ),
		'expected' => 'There was an error updating the plugin because your website is banned',
	],
	'testShouldReturnEmptyStringWhenBannedNotSet' => [
		'data'     => json_decode( json_encode( [
			'licence_expiration' => strtotime( 'next year' ),
		] ) ),
		'expected' => '',
	],
];
