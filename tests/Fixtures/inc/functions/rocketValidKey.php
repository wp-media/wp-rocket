<?php

$consumer_email = 'example@example.org';

return [
	'returns false when no secret key is set' => [
		'options'       => [
			'secret_key' => '',
		],
		'set_transient' => false,
		'expected'      => false,
	],
	'stores the sentinel without translating when the license data is invalid' => [
		'options'       => [
			'secret_key'     => 'not_a_matching_secret',
			'consumer_key'   => '12345678',
			'consumer_email' => $consumer_email,
		],
		'set_transient' => [ 'invalid_license_data' ],
		'expected'      => false,
	],
	'returns true when the license data is valid' => [
		'options'       => [
			'secret_key'     => hash( 'crc32', $consumer_email ),
			'consumer_key'   => '12345678',
			'consumer_email' => $consumer_email,
		],
		'set_transient' => false,
		'expected'      => true,
	],
];
