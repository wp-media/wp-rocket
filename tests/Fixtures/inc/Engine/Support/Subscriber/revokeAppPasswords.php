<?php

return [
	'wap_client_missing'         => [
		'config'   => [
			'wap_client_exists' => false,
		],
		'expected' => [],
	],
	'revoke_wp_rocket_passwords' => [
		'config'   => [
			'wap_client_exists' => true,
		],
		'expected' => [
			'product' => 'wprocket',
		],
	],
];
