<?php

return [
	'shouldReturnTrueWhenHasPageRule' => [
		'config' => [
			'zone_id' => '12345',
			'response' => (object) [
				'actions' => [
					'id' => 'cache_everything',
				],
			],
			'action_value' => 'cache_everything',
		],
		'expected' => true,
	],
	'shouldReturnFalseWhenNotHasPageRule' => [
		'config' => [
			'zone_id' => '12345',
			'response' => (object) [
				'actions' => [
					'id' => 'browser_check',
				],
			],
			'action_value' => 'cache_everything',
		],
		'expected' => false,
	],
	'shouldReturnWPErrorWhenException' => [
		'config' => [
			'zone_id' => '12345',
			'response' => 'exception',
			'action_value' => 'cache_everything',
		],
		'expected' => 'error',
	],
];
