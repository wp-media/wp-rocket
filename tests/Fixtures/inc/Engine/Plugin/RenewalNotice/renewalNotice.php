<?php
$json_not_expired = '{"ID":"129383","first_name":"Roger","email":"roger@wp-rocket.me","date_created":"1586207688","licence_account":"-1","licence_version":"3.7.5","licence_expiration":"2221418576","consumer_key":"d89e18ee","is_blacklisted":"","is_staggered":"","status":"expired","is_blocked":"","has_auto_renew":"","renewal_url":"https:\/\/wp-rocket.me\/checkout\/renew\/roger@wp-rocket.me\/da5891162a3bc2d8a9670267fd07c9eb\/","upgrade_plus_url":"https:\/\/wp-rocket.me\/checkout\/upgrade\/roger@wp-rocket.me\/d89e18ee\/plus\/","upgrade_infinite_url":"https:\/\/wp-rocket.me\/checkout\/upgrade\/roger@wp-rocket.me \/d89e18ee\/infinite\/"}';
$json_expired = '{"ID":"129383","first_name":"Roger","email":"roger@wp-rocket.me","date_created":"1586207688","licence_account":"-1","licence_version":"3.7.5","licence_expiration":"1557792000","consumer_key":"d89e18ee","is_blacklisted":"","is_staggered":"","status":"expired","is_blocked":"","has_auto_renew":"","renewal_url":"https:\/\/wp-rocket.me\/checkout\/renew\/roger@wp-rocket.me\/da5891162a3bc2d8a9670267fd07c9eb\/","upgrade_plus_url":"https:\/\/wp-rocket.me\/checkout\/upgrade\/roger@wp-rocket.me\/d89e18ee\/plus\/","upgrade_infinite_url":"https:\/\/wp-rocket.me\/checkout\/upgrade\/roger@wp-rocket.me \/d89e18ee\/infinite\/"}';

return [
	'testShouldDoNothingWhenLicenseNotExpired' => [
		'config' => [
			'user' => json_decode( $json_not_expired ),
			'license_expired' => false,
			'renewal_url' => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
			'current_version' => '3.13',
			'version' => '3.14',
		],
		'expected' => [
			'data' => [],
			'output' => '',
		],
	],
	'testShouldDoNothingWhenNoNewMajorVersion' => [
		'config' => [
			'user' => json_decode( $json_expired ),
			'license_expired' => true,
			'renewal_url' => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
			'current_version' => '3.13',
			'version' => '3.13',
		],
		'expected' => [
			'data' => [],
			'output' => '',
		],
	],
	'testShouldEchoNoticeWhenNewMajorVersion' => [
		'config' => [
			'user' => json_decode( $json_expired ),
			'license_expired' => true,
			'renewal_url' => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
			'current_version' => '3.13',
			'version' => '3.14.3',
		],
		'expected' => [
			'data' => [
				'version' => '3.14',
				'release_url' => 'https://wp-rocket.me/blog/3-14/',
				'renew_url' => 'https://wp-rocket.me/checkout/renew/roger@wp-rocket.me/da5891162a3bc2d8a9670267fd07c9eb/',
			],
			'output' => 'wp-rocket-update',
		],
	],
];
