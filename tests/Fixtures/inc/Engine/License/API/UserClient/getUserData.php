<?php

$json = '{"ID":"129383","first_name":"Roger","email":"roger@wp-rocket.me","date_created":"1586207688","licence_account":"3","licence_version":"3.7.2","licence_expiration":"1612310400","consumer_key":"d89e18ee","is_blacklisted":"","is_staggered":"","status":"active","is_blocked":"","has_auto_renew":"","renewal_url":"https:\/\/wp-rocket.me\/checkout\/renew\/roger@wp-rocket.me\/da5891162a3bc2d8a9670267fd07c9eb\/","upgrade_plus_url":"https:\/\/wp-rocket.me\/checkout\/upgrade\/roger@wp-rocket.me \/d89e18ee\/plus\/","upgrade_infinite_url":"https:\/\/wp-rocket.me\/checkout\/upgrade\/roger@wp-rocket.me \/d89e18ee\/infinite\/"}';
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
				'code' => 404,
				'body' => false,
			],
		],
		'expected' => false,
	],
	'testShouldReturnDataWhenCached'   => [
		'config'   => [
			'transient' => true,
			'response'  => false,
		],
		'expected' => $data,
	],
	'testShouldReturnDataWhenSuccess'  => [
		'config'   => [
			'transient' => false,
			'response'  => [
				'code' => 200,
				'body' => $json,
			],
		],
		'expected' => $data,
	],
];

