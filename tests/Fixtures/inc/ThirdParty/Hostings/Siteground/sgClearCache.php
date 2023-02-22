<?php
return [
	'invalidNonceShouldBailOut' => [
		'config' => [
			'sanitize_key' => 'sanitize_key',
			'nonce' => false,
			'has_right' => true,
		],
		'expected' => [
			'sanitize_key' => 'sanitize_key',
		]
	],
	'noRightsShouldBailOut' => [
		'config' => [
			'sanitize_key' => 'sanitize_key',
			'nonce' => true,
			'has_right' => false,
		],
		'expected' => [
			'sanitize_key' => 'sanitize_key',
		]
	],
	'validShouldClean' => [
		'config' => [
			'sanitize_key' => 'sanitize_key',
			'nonce' => true,
			'has_right' => true,
		],
		'expected' => [
			'sanitize_key' => 'sanitize_key',
		]
	],
];
