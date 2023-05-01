<?php

return [
	'testShouldReturnNullWhenNoCap' => [
		'config' => [
			'cap' => false,
			'transient' => [
				'result' => '',
				'message' => '',
			],
		],
		'expected' => '',
	],
	'testShouldReturnNullWhenNoTransient' => [
		'config' => [
			'cap' => true,
			'transient' => false,
		],
		'expected' => '',
	],
	'testShouldReturnNoticeWhenTransient' => [
		'config' => [
			'cap' => true,
			'transient' => [
				'result' => 'success',
				'message' => '<strong>WP Rocket:</strong> Cloudflare cache successfully purged.',
			],
		],
		'expected' => '<strong>WP Rocket:</strong> Cloudflare cache successfully purged.',
	],
];
