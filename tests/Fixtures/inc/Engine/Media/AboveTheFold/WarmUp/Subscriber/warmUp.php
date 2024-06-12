<?php
return [
	'testShouldCallSendToSaas' => [
		'config'   => [
			'wp_env' => 'production',
			'remove_unused_css' => 0,
			'is_allowed' => true,
			'links' => [
				'http://example.com/link1',
				'http://example.com/link2',
			],
		],
		'expected' => 2,
	],
	'testShouldNotCallSendToSaasWhenLocalEnv' => [
		'config'   => [
			'wp_env' => 'local',
			'remove_unused_css' => 0,
			'is_allowed' => true,
			'links' => [
			],
		],
		'expected' => 0,
	],
	'testShouldNotCallSendToSaasWhenRemoveUnusedCssEnabled' => [
		'config'   => [
			'wp_env' => 'production',
			'remove_unused_css' => 1,
			'is_allowed' => true,
			'links' => [
			],
		],
		'expected' => 0,
	],
	'testShouldNotCallSendToSaasWhenNotAllowed' => [
		'config'   => [
			'wp_env' => 'production',
			'remove_unused_css' => 0,
			'is_allowed' => false,
			'links' => [
			],
		],
		'expected' => 0,
	],
];
