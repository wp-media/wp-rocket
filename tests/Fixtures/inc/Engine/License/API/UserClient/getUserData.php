<?php

$json = '{"ID":"129383","first_name":"Roger","email":"roger@wp-rocket.me","date_created":"1586207688","licence_account":"-1","licence_version":"3.7.5","licence_expiration":"1557792000","consumer_key":"d89e18ee","is_blacklisted":"","is_staggered":"","status":"expired","is_blocked":"","has_auto_renew":"","renewal_url":"https:\/\/wp-rocket.me\/checkout\/renew\/roger@wp-rocket.me\/da5891162a3bc2d8a9670267fd07c9eb\/","upgrade_plus_url":"https:\/\/wp-rocket.me\/checkout\/upgrade\/roger@wp-rocket.me\/d89e18ee\/plus\/","upgrade_infinite_url":"https:\/\/wp-rocket.me\/checkout\/upgrade\/roger@wp-rocket.me \/d89e18ee\/infinite\/"}';
$data = json_decode( $json );

return [
	'testShouldReturnFalseWhenWPError' => [
		'config'   => [
			'transient' => false,
			'response'  => new WP_Error( 'http_request_failed', 'error' ),
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenNot200'  => [
		'config'   => [
			'transient' => false,
			'response'  => [
				'headers' => [],
				'body' => 'error 404',
				'response' => [
					'code' => 404,
				],
				'cookies' => [],
				'filename' => '',
			],
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenNoBody'  => [
		'config'   => [
			'transient' => false,
			'response'  => [
				'headers' => [],
				'body' => '',
				'response' => [
					'code' => 200,
				],
				'cookies' => [],
				'filename' => '',
			],
		],
		'expected' => false,
	],
	'testShouldReturnDataWhenCached'   => [
		'config'   => [
			'transient' => true,
			'response'  => [
				'headers' => [],
				'body' => $json,
				'response' => [
					'code' => 200,
				],
				'cookies' => [],
				'filename' => '',
			],
		],
		'expected' => $data,
	],
	'testShouldReturnDataWhenSuccess'  => [
		'config'   => [
			'transient' => false,
			'response'  => [
				'headers' => [],
				'body' => $json,
				'response' => [
					'code' => 200,
				],
				'cookies' => [],
				'filename' => '',
			],
		],
		'expected' => $data,
	],
];

