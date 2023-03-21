<?php

return [
	'shouldReturnValueOnWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'value'   => 1,
			'setting' => 'on',
			'response' => (object) [
				'succcess' => true,
			],
		],
		'expected' => 'on',
	],
	'shouldReturnValueOffWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'value'   => 0,
			'setting' => 'off',
			'response' => (object) [
				'succcess' => true,
			],
		],
		'expected' => 'off',
	],
	'shouldReturnWPErrorWhenException' => [
		'config' => [
			'zone_id' => '12345',
			'value' => 0,
			'setting' => 'off',
			'response' => 'exception',
		],
		'expected' => 'error',
	],
];
