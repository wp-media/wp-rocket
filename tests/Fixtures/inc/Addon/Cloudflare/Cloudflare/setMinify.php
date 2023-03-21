<?php

return [
	'shouldReturnValueWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'value'   => 'on',
			'setting' => [
				'css'  => 'on',
				'html' => 'off',
				'js'   => 'on',
			],
			'response' => (object) [
				'succcess' => true,
			],
		],
		'expected' => 'on',
	],
	'shouldReturnWPErrorWhenException' => [
		'config' => [
			'zone_id' => '12345',
			'value' => 'off',
			'setting' => [
				'css'  => 'off',
				'html' => 'off',
				'js'   => 'off',
			],
			'response' => 'exception',
		],
		'expected' => 'error',
	],
];
