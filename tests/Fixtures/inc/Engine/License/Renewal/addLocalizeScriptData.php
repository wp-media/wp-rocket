<?php

return [
	'testShouldReturnDefaultWhenAutoRenewTrue' => [
		'config'   => [
			'auto_renew'         => true,
			'license_expired'    => false,
			'licence_expiration' => strtotime( 'next year' ),
		],
		'data'    => [
			'nonce'      => 12345,
			'origin_url' => 'https://api.wp-rocket.me',
		],
		'expected' => [
			'nonce'      => 12345,
			'origin_url' => 'https://api.wp-rocket.me',
		],
	],
	'testShouldReturnDefaultWhenLicenseIsExpired' => [
		'config'   => [
			'auto_renew'         => false,
			'license_expired'    => true,
			'licence_expiration' => strtotime( 'last week' ),
		],
		'data'    => [
			'nonce'      => 12345,
			'origin_url' => 'https://api.wp-rocket.me',
		],
		'expected' => [
			'nonce'      => 12345,
			'origin_url' => 'https://api.wp-rocket.me',
		],
	],
	'testShouldReturnDefaultWhenLicenseIsNotSoonExpired' => [
		'config'   => [
			'auto_renew'         => false,
			'license_expired'    => false,
			'licence_expiration' => strtotime( 'next year' ),
		],
		'data'    => [
			'nonce'      => 12345,
			'origin_url' => 'https://api.wp-rocket.me',
		],
		'expected' => [
			'nonce'      => 12345,
			'origin_url' => 'https://api.wp-rocket.me',
		],
	],
	'testShouldReturnUpdatedArrayLicenseSoonExpired' => [
		'config'   => [
			'auto_renew'         => false,
			'license_expired'    => false,
			'licence_expiration' => strtotime( 'next week' ),
		],
		'data'    => [
			'nonce'      => 12345,
			'origin_url' => 'https://api.wp-rocket.me',
		],
		'expected' => [
			'nonce'              => 12345,
			'origin_url'         => 'https://api.wp-rocket.me',
			'license_expiration' => strtotime( 'next week' ),
		],
	],
];
