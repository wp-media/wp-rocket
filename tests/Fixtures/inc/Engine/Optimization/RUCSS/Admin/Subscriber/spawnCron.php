<?php

return [
	'RunSpawnCron' => [
		'config' => [
			'rocket_get_constant' => false,
			'current_user_can' => true
		],
		'expected' => [
			'spawnCronCalled' => 1,
			'wp_send_json_error' => 0,
		],
	],
	'RunWpCronDisabled' => [
		'config' => [
			'rocket_get_constant' => true,
			'current_user_can' => false
		],
		'expected' => [
			'spawnCronCalled' => 0,
			'wp_send_json_error' => 0,
		],
	],
	'RunUserCannot' => [
		'config' => [
			'rocket_get_constant' => false,
			'current_user_can' => false
		],
		'expected' => [
			'spawnCronCalled' => 0,
			'wp_send_json_error' => 1,
		],
	],
];

